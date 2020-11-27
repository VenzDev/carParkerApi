<?php

namespace App\Http\Controllers;

use App\Mail\NotificationMail;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Output\ConsoleOutput;

class CheckParkingController extends Controller
{
    public function checkParking(Request $request)
    {
        $delta = 10;

        $from = $request['from'];
        $to = $request['to'];

        $from_carbon = Carbon::parse($request['from']);
        $to_carbon = Carbon::parse($request['to']);
        $from_delta = Carbon::parse($request['from'])->addMinutes($delta);
        $minutes = $from_carbon->diffInMinutes($to_carbon);
        $iterations = $minutes / $delta;

        $array = [];

        for ($i=0; $i < 23; $i++) { 
           array_push($array, 'BUSY');
        }

        for ($i=0; $i < 23 ; $i++) { 
            for ($j=0; $j < $iterations; $j++) { 
                if (count($this->checkSides($from_carbon, $from_delta, $i +1)) === 0) {
                    $array[$i] = 'AVAILABLE_RESERVATION';
                    break;
                }
                $from_carbon->addMinutes(10);
                $from_delta->addMinutes(10);
            }
            $from_delta = Carbon::parse($request['from'])->addMinutes($delta);
        }

        for ($i=0; $i < 23; $i++) { 
            if(count($this->generalCheckByParkingId($from, $to,$i + 1)) === 0)
            {
                $array[$i] = 'FREE';
            }
        }

        return response($array);
    }

    public function availableReservations(Request $request)
    {
        $delta = 10;

        $parking_id = (int) $request['parking_slot_id'];
        $from = Carbon::parse($request['from']);
        $to = Carbon::parse($request['to']);

        $minutes = $from->diffInMinutes($to);
        $iterations = $minutes / $delta;
        $from_delta = Carbon::parse($request['from'])->addMinutes($delta);

        $available_reservations_array = [];

        for ($i = 0; $i < $iterations; $i++) {
            if (count($this->checkSides($from, $from_delta, $parking_id)) === 0) {
                array_push($available_reservations_array, [$from->format("Y-m-d H:i:s"), $from_delta->format("Y-m-d H:i:s")]);
            }
            $from->addMinutes(10);
            $from_delta->addMinutes(10);
        }
        $final_array = $this->mergeAvailableReservation($available_reservations_array);

        return response($final_array);
    }

    public function reserveSlot(Request $request)
    {

        $today = Carbon::now()->tz('Europe/Warsaw');

        $user_email = $request->user()->email;
        $system_from = Carbon::parse($request['from']);
        $system_to = Carbon::parse($request['to']);

        $has_reservation = $this->hasUserReservationInTime($system_from, $system_to, $request['user_id']);
        
        if($has_reservation){
            abort(422,'User has reservation in given time.');
        }
        
        $has_already_reserved = $this->generalCheckByParkingId($request['from'], $request['to'], $request['parking_slot_id']);

        if($has_already_reserved){
            abort(422,'This reservation is not available.');
        }

        $daily_user_reservations = $this->dailyUserReservations($request['user_id']);

        if($daily_user_reservations === 3){
            abort(422,'You have too much active reservations.');
        }

        if ($system_from->minute === 0) {
            $system_from->subHour();
            $system_from->setMinutes(55);
        } else {
            $system_from->subMinutes(5);
        }

        $system_to->addMinutes(5);
        
        $slot = new Reservation();
        if($system_from->diffInMinutes($today) < 60){
            $slot->notify_sent = true;
        } else {
            $slot->notify_sent = false;
        }

        $slot->user_id = $request['user_id'];
        $slot->reservation_from = $request['from'];
        $slot->system_reservation_from = $system_from;
        $slot->system_reservation_to = $system_to;
        $slot->reservation_to = $request['to'];
        $slot->parking_slot_id = (int) $request['parking_slot_id'];
        $slot->status = 'RESERVED';

        $slot->save();

        $details = [
            'title' => 'Reservation success',
            'body' => "Reservation from $slot->reservation_from to $slot->reservation_to, parking slot: $slot->parking_slot_id"
        ];
    
        Mail::to($user_email)->send(new NotificationMail('Reservation - Parker', $details));

        return response()->json(['status' => 'success']);
    }

    private function generalCheck($from, $to)
    {
        $reservations_between = Reservation::all()
        ->where('status', 'RESERVED')
        ->where('system_reservation_from', '>=', date($from))
        ->where('system_reservation_to', '<=', date($to));

        $reservations_left = Reservation::all()
        ->where('status', 'RESERVED')
        ->filter(function ($item) use (&$from) {
            if (Carbon::parse($from)->between($item->system_reservation_from, $item->system_reservation_to)) {
                return $item;
            }
        });

        $reservations_right = Reservation::all()
        ->where('status', 'RESERVED')
        ->filter(function ($item) use (&$to) {
            if (Carbon::parse($to)->between($item->system_reservation_from, $item->system_reservation_to)) {
                return $item;
            }
        });

        $merged = array_merge(
            $reservations_left->toArray(),
            $reservations_right->toArray(),
            $reservations_between->toArray()
        );

        $unique = array_unique($merged, SORT_REGULAR);
        $final = array_merge($unique, array());
        return $final;
    }

    private function dailyUserReservations(int $user_id){
        $today_from = Carbon::now()->tz('Europe/Warsaw');
        $today_from->setHour(0);

        $today_to = Carbon::now()->tz('Europe/Warsaw');
        $today_to->setHour(24);
        $count = Reservation::all()
        ->where('status', 'RESERVED')
        ->where('user_id',$user_id)
        ->where('system_reservation_from', '>=', date($today_from))
        ->where('system_reservation_to', '<=', date($today_to))->count();

        return $count;
    }

    private function generalCheckByParkingId(string $from, string $to, int $parking_id)
    {
        $reservations_between = Reservation::all()
        ->where('status', 'RESERVED')
        ->where('parking_slot_id', $parking_id)
        ->where('system_reservation_from', '>=', date($from))
        ->where('system_reservation_to', '<=', date($to));

        $reservations_left = Reservation::all()
        ->where('status', 'RESERVED')
        ->where('parking_slot_id', $parking_id)
        ->filter(function ($item) use (&$from) {
            if (Carbon::parse($from)->between($item->system_reservation_from, $item->system_reservation_to)) {
                return $item;
            }
        });

        $reservations_right = Reservation::all()
        ->where('status', 'RESERVED')
        ->where('parking_slot_id', $parking_id)
        ->filter(function ($item) use (&$to) {
            if (Carbon::parse($to)->between($item->system_reservation_from, $item->system_reservation_to)) {
                return $item;
            }
        });

        $merged = array_merge(
            $reservations_left->toArray(),
            $reservations_right->toArray(),
            $reservations_between->toArray()
        );

        $unique = array_unique($merged, SORT_REGULAR);
        $final = array_merge($unique, array());
        return $final;
    }

    private function hasUserReservationInTime(Carbon $from, Carbon $to, int $user_id)
    {
        for ($i=1; $i < 24; $i++) { 
            $reservations_between = Reservation::all()
            ->where('status', 'RESERVED')
            ->where('parking_slot_id', $i)
            ->where('user_id', $user_id)
            ->where('system_reservation_from', '>=', date($from))
            ->where('system_reservation_to', '<=', date($to));
    
            $reservations_left = Reservation::all()
            ->where('status', 'RESERVED')
            ->where('user_id', $user_id)
            ->where('parking_slot_id', $i)
            ->filter(function ($item) use (&$from) {
                if (Carbon::parse($from)->between($item->system_reservation_from, $item->system_reservation_to)) {
                    return $item;
                }
            });
    
            $reservations_right = Reservation::all()
            ->where('status', 'RESERVED')
            ->where('user_id', $user_id)
            ->where('parking_slot_id', $i)
            ->filter(function ($item) use (&$to) {
                if (Carbon::parse($to)->between($item->system_reservation_from, $item->system_reservation_to)) {
                    return $item;
                }
            });
    
            $merged = array_merge(
                $reservations_left->toArray(),
                $reservations_right->toArray(),
                $reservations_between->toArray()
            );
    
            $unique = array_unique($merged, SORT_REGULAR);
            $final = array_merge($unique, array());
            
            if(count($final) > 0){
                return true;
            }
        }
        return false;
    }

    private function checkSides(Carbon $from, Carbon $to, int $parking_id, int $user_id = null)
    {
        $reservations_left = Reservation::all()
        ->where('status', 'RESERVED')
        ->where('parking_slot_id', $parking_id)
        ->filter(function ($item) use (&$from) {
            if (Carbon::parse($from)->between($item->system_reservation_from, $item->system_reservation_to)) {
                return $item;
            }
        });

        $reservations_right = Reservation::all()
        ->where('status', 'RESERVED')
        ->where('parking_slot_id', $parking_id)
        ->filter(function ($item) use (&$to) {
            if (Carbon::parse($to)->between($item->system_reservation_from, $item->system_reservation_to)) {
                return $item;
            }
        });

        $merged = array_merge(
            $reservations_left->toArray(),
            $reservations_right->toArray(),
        );

        $unique = array_unique($merged, SORT_REGULAR);
        $final = array_merge($unique, array());
        return $final;
    }

    public function carsOnParking()
    {
        $carsOnParking = Reservation::query()->where('status', 'CAR ON PARKING')->get()->count();
        return response()->json($carsOnParking);
    }

    public function cancelReservation(Request $request)
    {
        $reservation_id = $request['reservation_id'];

        $reservation = Reservation::query()->where('id', $reservation_id)->first();
        if ($reservation && $reservation->can_cancel) {
            $reservation->delete();
            return response()->json(['status' => 'success']);
        }
        return response()->setStatusCode(400);
    }

    private function mergeAvailableReservation($array)
    {
        $new_array = [];
        $merge_count = 0;
        $arr_length = count($array);

        if ($arr_length === 0) {
            return [];
        } else if ($arr_length === 1) {
            return $array;
        } else if ($arr_length === 2) {
            if ($array[0][1] === $array[1][0]) {
                array_push($new_array, [$array[0][0], $array[1][1]]);
                return $new_array;
            } else {
                return $array;
            }
        } else {
            for ($index = 0; $index < $arr_length; $index++) {
                if ($index + 1 !== $arr_length) {
                    if ($array[$index][1] !== $array[$index + 1][0]) {
                        array_push($new_array, [$array[$index - $merge_count][0], $array[$index][1]]);
                        $merge_count = 0;
                    } else {
                        $merge_count++;
                    }
                } else {
                    if ($merge_count > 0) {
                        array_push($new_array, [$array[$index - $merge_count][0], $array[$index][1]]);
                        break;
                    } else {
                        array_push($new_array, $array[$index]);
                        break;
                    }
                }
            }
        }
            return $new_array;
    }

    public function percentageStatus(Request $request)
    {
        $reservation_minutes = 0;
        $array = [];

        $from = Carbon::parse($request['from']);
        $to = Carbon::parse($request['to']);

        $reservations = Reservation::all()        
        ->filter(function ($item) use (&$from, &$to) {
            $sys_date = Carbon::parse($item->system_reservation_to);
            if ($sys_date->day >= $from->day && $sys_date->day <= $to->day) {
                return $item;
            }
        });
        foreach ($reservations as $reservation) {
            $system_reservation_to = Carbon::parse($reservation->system_reservation_to);
            $system_reservation_from = Carbon::parse($reservation->system_reservation_from);

            $diff = $system_reservation_to->diffInMinutes($system_reservation_from);

            $reservation_minutes+=$diff;
        }
        if($reservation_minutes === 0)
        {
            for ($i=0; $i < 23; $i++) { 
                array_push($array,0);
            }
            return response()->json(['percentage' => 0, 'slots_percentage' => $array]);
        }

        $diff_in_days = $to->diffInDays($from);

        $percentage = round((($reservation_minutes / (24 * 60 * 23 * ($diff_in_days + 1))) * 100), 2);
        for ($i=0; $i < 23; $i++) { 
            $value = $this->checkSlot($i+1, $from, $to);
            array_push($array,$value);
        }
        return response()->json(['percentage' => $percentage, 'slots_percentage' => $array]);
    }

    private function checkSlot(int $slot_id, Carbon $from, Carbon $to)
    {
        $reservation_minutes = 0;
        $reservations = Reservation::all()
        ->where('parking_slot_id', $slot_id)
        ->filter(function ($item) use (&$from, &$to) {
            $sys_date = Carbon::parse($item->system_reservation_to);
            if ($sys_date->day >= $from->day && $sys_date->day <= $to->day) {
                return $item;
            }
        });

        foreach ($reservations as $reservation) {
            $system_reservation_to = Carbon::parse($reservation->system_reservation_to);
            $system_reservation_from = Carbon::parse($reservation->system_reservation_from);

            $diff = $system_reservation_to->diffInMinutes($system_reservation_from);

            $reservation_minutes+=$diff;
        }
        
        if($reservation_minutes === 0)
        {
            return 0;
        }

        $diff_in_days = $to->diffInDays($from);

        return round((($reservation_minutes / (24 * 60 * ($diff_in_days + 1))) * 100), 2);
    }
}

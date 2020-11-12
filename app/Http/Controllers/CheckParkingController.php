<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

class CheckParkingController extends Controller
{
    public function checkParking(Request $request){
        $from = $request['from'];
        $to = $request['to'];

        $final = $this->generalCheck($from, $to);


        return response($final);
    }

    public function availableReservations(Request $request)
    {
        $delta = 10;

        $parking_id = $request['parking_slot_id'];
        $from = Carbon::parse($request['from']);;
        $to = Carbon::parse($request['to']);

        $minutes = $from->diffInMinutes($to);
        $iterations = $minutes / $delta;
        $from_delta = Carbon::parse($request['from'])->addMinutes($delta);

        $available_reservations_array = [];

        for ($i=0; $i < $iterations; $i++) {
            if(count($this->checkSides($from, $from_delta, $parking_id)) === 0)
            {
                array_push($available_reservations_array, [$from->format("Y-m-d H:i:s"), $from_delta->format("Y-m-d H:i:s")]);
            }
            $from->addMinutes(10);
            $from_delta->addMinutes(10);
        }
        $final_array = $this->mergeAvailableReservation($available_reservations_array);

        return response($final_array);
    }

    public function reserveSlot(Request $request){

        $output = new ConsoleOutput();

        $system_from = Carbon::parse($request['from']);
        $system_to = Carbon::parse($request['to']);

        if ($system_from->minute===0)
        {
            $system_from->subHour();
            $system_from->setMinutes(55);
        }
        else
        {
            $system_from->subMinutes(5);
        }

        $system_to->addMinutes(5);


        $slot = new Reservation();
        $slot->user_id = $request['user_id'];
        $slot->reservation_from = $request['from'];
        $slot->system_reservation_from = $system_from;
        $slot->system_reservation_to = $system_to;
        $slot->reservation_to = $request['to'];
        $slot->parking_slot_id = $request['parking_slot_id'];
        $slot->status='RESERVED';

        $slot->save();


        return response()->json(['status'=>'success']);
    }

    private function generalCheck($from,$to)
    {
        $reservations_between =  Reservation::all()
        ->where('status','RESERVED')
        ->where('system_reservation_from','>=', date($from))
        ->where('system_reservation_to','<=', date($to));

        $reservations_left =  Reservation::all()
        ->where('status', 'RESERVED')
        ->filter(function($item) use(&$from) {
            if(Carbon::parse($from)->between($item->system_reservation_from, $item->system_reservation_to))
            {
                return $item;
            }
          });

        $reservations_right =  Reservation::all()
        ->where('status', 'RESERVED')
        ->filter(function($item) use(&$to) {
            if(Carbon::parse($to)->between($item->system_reservation_from, $item->system_reservation_to))
            {
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

    private function checkSides(Carbon $from, Carbon $to, $parking_id)
    {
        $reservations_left =  Reservation::all()
        ->where('status', 'RESERVED')
        ->where('parking_slot_id', $parking_id)
        ->filter(function($item) use(&$from) {
            if(Carbon::parse($from)->between($item->system_reservation_from, $item->system_reservation_to))
            {
                return $item;
            }
          });

        $reservations_right =  Reservation::all()
        ->where('status', 'RESERVED')
        ->where('parking_slot_id', $parking_id)
        ->filter(function($item) use(&$to) {
            if(Carbon::parse($to)->between($item->system_reservation_from, $item->system_reservation_to))
            {
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
        $carsOnParking = Reservation::query()->where('status','CAR ON PARKING')->get()->count();
        return response()->json($carsOnParking);
    }

    public function cancelReservation(Request $request)
    {
        $reservation_id = $request['reservation_id'];

        $reservation = Reservation::query()->where('id',$reservation_id)->first();
        if($reservation && $reservation->can_cancel)
        {
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

        if($arr_length === 0)
        {
            return [];
        }
        else if($arr_length === 1)
        {
            return $array;
        }
        else if($arr_length === 2)
        {
            if($array[0][1] === $array[1][0])
            {
                array_push($new_array, [$array[0][0], $array[1][1]]);
                return $new_array;
            }
            else
            {
                return $array;
            }
        }
        else
        {
            for ($index=0; $index < $arr_length; $index++) 
            {
                if($index+1 !== $arr_length)
                {
                    if($array[$index][1] !== $array[$index + 1][0])
                    {
                        array_push($new_array, [$array[$index - $merge_count][0], $array[$index][1]]);
                        $merge_count = 0;
                    }
                    else
                    {
                        $merge_count++;
                    }
                }
                else
                {
                    if($merge_count > 0)
                    {
                        array_push($new_array, [$array[$index - $merge_count][0], $array[$index][1]]);
                        break;
                    }
                    else
                    {
                        array_push($new_array, $array[$index]);
                        break;
                    }
                } 
            }
        }
            Log::info("-------------------------------------------------------------");
            Log::info($array);
            Log::info("-------------------------------------------------------------");
            Log::info($new_array);
            return $new_array;
        }
}

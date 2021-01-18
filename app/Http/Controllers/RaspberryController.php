<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\NotificationMail;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RaspberryController extends Controller
{
    public function raspberry(Request $request)
    {
        $rfid = $request['rfid'];
        $user = User::all()->where('rfid_card_id', $rfid)->first();

        if (!$user) {
            return response()->json(['status' => 'RFID not found']);
        }

        $now = Carbon::now();

        $active_reservation = Reservation::all()
        ->where('user_id', $user->id)
        ->where('status', 'RESERVED')
        ->filter(function ($item) use (&$now) {
            if ($now->between($item->system_reservation_from, $item->reservation_to)) {
                return $item;
            }
        })
        ->first();

        if ($active_reservation) {
            $car_on_parking_reservation = Reservation::all()
            ->where('parking_slot_id', $active_reservation->parking_slot_id)
            ->where('status', 'CAR ON PARKING')->first();

            if ($car_on_parking_reservation) {
                for ($i = 1; $i < 24; $i++) {
                    if (
                        count(CheckParkingController::generalCheckByParkingId(
                            $active_reservation->reservation_from,
                            $active_reservation->reservation_to,
                            $i
                        )) === 0
                    ) {
                            $active_reservation->parking_slot_id = $i;
                            $active_reservation->status = 'CAR ON PARKING';
                            $active_reservation->save();

                            $system_time_to_change = Carbon::parse($car_on_parking_reservation->system_reservation_to);
                            $time_to_change = Carbon::parse($car_on_parking_reservation->reservation_to);
                            
                            $time_to_change->addMinutes(10);
                            $system_time_to_change->addMinutes(10);
                            $car_on_parking_reservation->system_reservation_to = $system_time_to_change;
                            $car_on_parking_reservation->reservation_to = $time_to_change;
                            $car_on_parking_reservation->save();


                            return response()->json(['status' => 'Reservation confirmed']);
                            break;
                    }
                }

                $details = [
                    'title' => "Problem with car on parking slot: $active_reservation->parking_slot_id",
                    'body' => "Somebody have problem with car, user_id: $car_on_parking_reservation->user_id"
                ];

                $admin = User::all()->where('role', 'ADMIN')->first();
        
                Mail::to($admin->email)->send(new NotificationMail('Issue - Parker', $details));

                $active_reservation->status = 'CAR ON PARKING';
                $active_reservation->save();
                return response()->json(['status' => 'Reservation confirmed']);
            } else {
                $active_reservation->status = 'CAR ON PARKING';
                $active_reservation->save();
                return response()->json(['status' => 'Reservation confirmed']);
            }
        }

        $end_reservation = Reservation::all()
        ->where('user_id', $user->id)
        ->where('status', 'CAR ON PARKING')
        ->filter(function ($item) use (&$now) {
            if ($now->between($item->system_reservation_from, $item->system_reservation_to)) {
                return $item;
            }
        })
        ->first();

        if ($end_reservation) {
            $end_reservation->status = 'ARCHIVED';
            $end_reservation->save();
            return response()->json(['status' => 'Reservation ended']);
        }

        return response()->json(['status' => 'Reservation not found']);
    }
}

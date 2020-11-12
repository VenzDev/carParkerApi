<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RaspberryController extends Controller
{
    public function raspberry(Request $request)
    {
        $rfid = $request['rfid'];
        $user = User::all()->where('rfid_card_id', $rfid)->first();
    
        if(!$user)
        {
            return response()->json(['status'=>'RFID not found']);
        }
    
        $now = Carbon::now();
    
        $active_reservation = Reservation::all()
        ->where('user_id', $user->id)
        ->where('status', 'RESERVED')
        ->filter(function($item) use(&$now) {
            if($now->between($item->system_reservation_from, $item->reservation_to))
            {
                return $item;
            }
          })
        ->first();
    
        if($active_reservation)
        {
            $active_reservation->status = 'CAR ON PARKING';
            $active_reservation->save();
            return response()->json(['status' => 'Reservation confirmed']);
        }
    
        $end_reservation = Reservation::all()
        ->where('user_id', $user->id)
        ->where('status', 'CAR ON PARKING')
        ->filter(function($item) use(&$now) {
            if($now->between($item->system_reservation_from, $item->system_reservation_to))
            {
                return $item;
            }
          })
        ->first();
    
        if($end_reservation)
        {
            $end_reservation->status = 'ARCHIVED';
            $end_reservation->save();
            return response()->json(['status' => 'Reservation ended']);
        }
    
        return response()->json(['status' => 'Reservation not found']);
    
    
    }
}
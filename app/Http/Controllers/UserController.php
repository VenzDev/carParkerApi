<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function user(Request $request)
    {
        $user_id = $request->user()->id;
        $user = User::with(['reservations' => function ($query) {
            $query->whereIn('status', ['RESERVED', 'CAR ON PARKING']);
        }])->where('id', $user_id)->get()->first();
        $cars_on_parking = Reservation::query()->where('status', 'CAR ON PARKING')->get()->count();
        $user->setAttribute('cars_on_parking', $cars_on_parking);

        return response()->json($user);
    }

    public function activeReservations(Request $request)
    {
        $user_id = $request->user()->id;
        $active_reservations = Reservation::all()
        ->whereIn('status', ['RESERVED', 'CAR ON PARKING'])
        ->where('user_id', $user_id)
        ->toArray();


        $array = array_merge($active_reservations);

        return response()->json($array);
    }

    public function activeAccount(Request $request)
    {
        $rfid_card_id = $request->user()->rfid_card_id;
        $user_id = $request->user()->id;
        $rfid = $request['rfid'];
        if ($rfid_card_id === $rfid) {
            $user = User::query()->where('id', $user_id)->first();
            $user->isActive = true;
            $user->save();
            return response()->json(['status' => 'success']);
        }
        return response()->setStatusCode(400, 'problem with activation');
    }

    public function verifyAccount(Request $request)
    {
        $code = $request['code'];
        $user_id = $request->user()->id;

        if ($code === '1234') {
            $user = User::query()->where('id', $user_id)->first();
            $user->is_active = true;
            $user->save();
        } else {
            return response()->setStatusCode(400, 'problem with activation');
        }

        return response()->json(['status' => 'success']);
    }
}

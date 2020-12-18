<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function allActiveReservations(Request $request)
    {
        $all_reservations  = Reservation::query()
        ->whereIn('status', ['RESERVED', 'CAR ON PARKING'])->with('user:name,id')->simplePaginate(10);

        return response()->json($all_reservations);
    }

    public function allUsers(Request $request)
    {
        $all_users  = User::all()->simplePaginate(10);

        return response()->json($all_users);
    }

    public function deleteReservation(Request $request)
    {
        $reservation_id = $request['reservation_id'];

        Reservation::query()->where('id', $reservation_id)->delete();

        return response()->json(['status'=> 'success']);
    }

}

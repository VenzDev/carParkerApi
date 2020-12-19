<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $all_users  = User::all()->toArray();

        return response()->json($all_users);
    }

    public function deleteReservation(Request $request)
    {
        $reservation_id = $request['reservation_id'];

        Reservation::query()->where('id', $reservation_id)->delete();

        return response()->json(['status'=> 'success']);
    }

    public function deleteUser(Request $request)
    {
        $user_id = $request['user_id'];

        User::query()->where('id', $user_id)->first()->delete();

        return response()->json(['status' => 'success']);
    }

    public function editUser(Request $request)
    {
        $finded_user = User::query()->where('id', $request->id)->first();

        $finded_user->name = $request->name;
        $finded_user->email = $request->email;
        $finded_user->rfid_card_id = $request->rfid_card_id;
        $finded_user->is_active = $request->is_active;

        $finded_user->save();

        return response()->json(['status' => 'success']);
    }

}

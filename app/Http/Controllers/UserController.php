<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(Request $request)
    {
        $user_id = $request->user()->id;
        $user =User::with(['reservations' => function($query){
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
        ->whereIn('status',['RESERVED', 'CAR ON PARKING'])
        ->where('user_id', $user_id)
        ->toArray();
    
    
        $array= array_merge($active_reservations);
    
        return response()->json($array);
    }
}
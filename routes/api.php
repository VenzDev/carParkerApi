<?php

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {

    $user_id = $request->user()->id;
    $user =User::with(['reservations'=>function($query){
        $query->where('status','=','RESERVED');
    }])->where('id',$user_id)->get()->first();

    

    Log::info($user);

    return response()->json($user);
});

Route::get('/status',function(){
    return response()->json(['status'=>'ok','message'=>'hello world from carparker api (laravel)']);
});

Route::post('/register','App\Http\Controllers\RegisterController@register');

Route::post('/login','App\Http\Controllers\LoginController@login');

Route::post('/logout','App\Http\Controllers\LoginController@logout');

Route::post('/checkParking','App\Http\Controllers\CheckParkingController@checkParking');

Route::middleware('auth:sanctum')->get('/active_reservations',function(Request $request){
    $user_id = $request->user()->id;
    Log::info($user_id);
    $active_reservations = Reservation::all()
    ->where('status','RESERVED')
    ->where('user_id',$user_id)
    ->toArray();

    $array= array_merge($active_reservations);

    return response()->json($array);
});

Route::post('/reserveSlot','App\Http\Controllers\CheckParkingController@reserveSlot');

Route::post('/raspberry',function(Request $request){
    $rfid = $request['rfid'];
    $user = User::all()->where('rfid_card_id',$rfid)->first();

    if(!$user)
    {
        return response()->json(['status'=>'RFID not found']);
    }

    $now = Carbon::now();

    $active_reservation = Reservation::all()
    ->where('user_id',$user->id)
    ->where('status','RESERVED')
    ->where('reservation_to','>=',$now)
    ->first();

    if($active_reservation)
    {
        $active_reservation->status = 'CAR ON PARKING';
        $active_reservation->save();
        return response()->json(['status'=>'Reservation confirmed']);
    }

    $end_reservation = Reservation::all()
    ->where('user_id',$user->id)
    ->where('status','CAR ON PARKING')
    ->where('system_reservation_to','>=',$now)
    ->first();

    if($end_reservation)
    {
        $end_reservation->status = 'ARCHIVED';
        $end_reservation->save();
        return response()->json(['status'=>'Reservation ended']);
    }

    return response()->json(['status'=>'Reservation not found']);


});
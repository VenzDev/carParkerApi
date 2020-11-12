<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/status',function()
{
    return response()->json(['status'=>'ok','message'=>'hello world from carparker api (laravel)']);
});

Route::middleware('auth:sanctum')->get('/user','App\Http\Controllers\UserController@user');
Route::middleware('auth:sanctum')->get('/active_reservations','App\Http\Controllers\UserController@activeReservations');

Route::post('/register','App\Http\Controllers\RegisterController@register');

Route::post('/login','App\Http\Controllers\LoginController@login');

Route::middleware('auth:sanctum')->post('/logout','App\Http\Controllers\LoginController@logout');

Route::middleware('auth:sanctum')->post('/checkParking','App\Http\Controllers\CheckParkingController@checkParking');
Route::middleware('auth:sanctum')->post('/reserveSlot','App\Http\Controllers\CheckParkingController@reserveSlot');
Route::middleware('auth:sanctum')->get('/carsOnParking','App\Http\Controllers\CheckParkingController@carsOnParking');
Route::middleware('auth:sanctum')->post("/availableReservations", 'App\Http\Controllers\CheckParkingController@availableReservations');
Route::middleware('auth:sanctum')->post("/cancelReservation",'App\Http\Controllers\CheckParkingController@cancelReservation');

Route::post('/raspberry','App\Http\Controllers\RaspberryController@raspberry');
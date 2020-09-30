<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;

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
    $user_reservations =User::findOrFail($user_id)->with('reservations')->get()->first();


    return response()->json($user_reservations);
});

Route::get('/status',function(){
    return response()->json(['status'=>'ok','message'=>'hello world from carparker api (laravel)']);
});

Route::post('/register','App\Http\Controllers\RegisterController@register');

Route::post('/login','App\Http\Controllers\LoginController@login');

Route::post('/logout','App\Http\Controllers\LoginController@logout');

Route::post('/checkParking','App\Http\Controllers\CheckParkingController@checkParking');

Route::post('/reserveSlot','App\Http\Controllers\CheckParkingController@reserveSlot');

Route::get('/raspberry',function(){
    return response()->json(['status'=>'hello from raspberry']);
});
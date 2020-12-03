<?php

use App\Mail\TestMail;
use App\Models\Reservation;
use Facade\Ignition\QueryRecorder\Query;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
Route::middleware('auth:sanctum')->post("/percentageStatus",'App\Http\Controllers\CheckParkingController@percentageStatus');
Route::middleware('auth:sanctum')->post('/activeAccount','App\Http\UserController@activeAccount');
Route::post('/createTicket','App\Http\Controllers\TicketController@createTicket');
Route::get('/getTickets','App\Http\Controllers\TicketController@getTickets');
Route::middleware('auth:sanctum')->get('/getUserTicket','App\Http\Controllers\TicketController@getUserTicket');
Route::middleware('auth:sanctum')->post('/addTicketMessage','App\Http\Controllers\TicketController@addTicketMessage');
Route::middleware('auth:sanctum')->post('/getTicketById','App\Http\Controllers\TicketController@getTicketById');
Route::middleware('auth:sanctum')->post('/deleteTicket','App\Http\Controllers\TicketController@deleteTicket');
Route::middleware('auth:sanctum')->get('/allActiveReservations','App\Http\Controllers\AdminController@allActiveReservations');

Route::get('/test', function(){

    function xddd(Builder $query){
        Log::info($query->where('id', 2)->get());
    }

    $reservation = Reservation::query();
    xddd($reservation);
    response()->json(['status'=> 'alles gut']);
});

Route::post('/raspberry','App\Http\Controllers\RaspberryController@raspberry');
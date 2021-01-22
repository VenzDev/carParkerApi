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


Route::get('/status', function () {
    return response()->json(['status' => 'ok', 'message' => 'hello world from carparker api (laravel)']);
});

Route::post('/register', 'App\Http\Controllers\RegisterController@register');
Route::post('/login', 'App\Http\Controllers\LoginController@login');

Route::post('/raspberry', 'App\Http\Controllers\RaspberryController@raspberry');

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('can:isAdmin')->group(function () {
        Route::get('/adminCarsOnParking', 'App\Http\Controllers\CheckParkingController@adminCarsOnParking');
        Route::get('/allActiveReservations', 'App\Http\Controllers\AdminController@allActiveReservations');
        Route::get('/allUsers', 'App\Http\Controllers\AdminController@allUsers');
        Route::post('/deleteReservation', 'App\Http\Controllers\AdminController@deleteReservation');
        Route::post('/editUser', 'App\Http\Controllers\AdminController@editUser');
        Route::post('/deleteUser', 'App\Http\Controllers\AdminController@deleteUser');
    });

    Route::get('/user', 'App\Http\Controllers\UserController@user');
    Route::get('/active_reservations', 'App\Http\Controllers\UserController@activeReservations');
    Route::post('/logout', 'App\Http\Controllers\LoginController@logout');
    Route::post('/checkParking', 'App\Http\Controllers\CheckParkingController@checkParking');
    Route::post('/reserveSlot', 'App\Http\Controllers\CheckParkingController@reserveSlot');
    Route::get('/carsOnParking', 'App\Http\Controllers\CheckParkingController@carsOnParking');
    Route::post("/availableReservations", 'App\Http\Controllers\CheckParkingController@availableReservations');
    Route::post("/cancelReservation", 'App\Http\Controllers\CheckParkingController@cancelReservation');
    Route::post("/percentageStatus", 'App\Http\Controllers\CheckParkingController@percentageStatus');
    Route::post('/activeAccount', 'App\Http\UserController@activeAccount');
    Route::get('/getUserTicket', 'App\Http\Controllers\TicketController@getUserTicket');
    Route::post('/getTicketById', 'App\Http\Controllers\TicketController@getTicketById');
    Route::post('/addTicketMessage', 'App\Http\Controllers\TicketController@addTicketMessage');
    Route::post('/setTicketAsFinished', 'App\Http\Controllers\TicketController@setTicketAsFinished');
    Route::post('/deleteTicket', 'App\Http\Controllers\TicketController@deleteTicket');
    Route::Post('/verifyAccount', 'App\Http\Controllers\UserController@verifyAccount');
    Route::post('/createTicket', 'App\Http\Controllers\TicketController@createTicket');
    Route::get('/getTickets', 'App\Http\Controllers\TicketController@getTickets');
})

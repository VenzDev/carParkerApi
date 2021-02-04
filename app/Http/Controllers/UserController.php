<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userRepository;
    private $reservationRepository;

    public function __construct(UserRepository $userRepository, ReservationRepository $reservationRepository)
    {
        $this->userRepository = $userRepository;
        $this->reservationRepository = $reservationRepository;
    }

    public function user(Request $request)
    {
        $user_id = $request->user()->id;
        $user = $this->userRepository->findWithActiveReservations($user_id);

        $cars_on_parking = $this->reservationRepository->carsOnParking();

        $user->setAttribute('cars_on_parking', $cars_on_parking);

        return response()->json($user);
    }

    public function activeReservations(Request $request)
    {
        $user_id = $request->user()->id;
        $active_reservations = $this->reservationRepository
        ->activeUserReservations($user_id)
        ->toArray();

        $array = array_merge($active_reservations);

        return response()->json($array);
    }

    public function activeAccount(Request $request)
    {
        $rfid_card_id = $request->user()->rfid_card_id;
        $user_id = $request->user()->id;
        $rfid = $request->rfid;

        if ($rfid_card_id === $rfid) {
            $this->userRepository->activeUser($user_id);
            return response()->json(['status' => 'success']);
        }

        return response()->setStatusCode(400, 'problem with activation');
    }

    public function verifyAccount(Request $request)
    {
        $code = $request->code;
        $user_id = $request->user()->id;

        if ($code === '1234') {
            $this->userRepository->activeUser($user_id);
        } else {
            return response()->setStatusCode(400, 'problem with activation');
        }

        return response()->json(['status' => 'success']);
    }
}

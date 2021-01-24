<?php

namespace App\Http\Controllers;

use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    private UserRepository $userRepository;
    private ReservationRepository $reservationRepository;

    public function __construct(UserRepository $userRepository, ReservationRepository $reservationRepository)
    {
        $this->userRepository = $userRepository;
        $this->reservationRepository = $reservationRepository;
    }

    public function allActiveReservations()
    {
        $all_reservations  = $this->reservationRepository->activeReservations('user:name,id', 20);

        return response()->json($all_reservations);
    }

    public function allUsers()
    {
        $all_users  = $this->userRepository->all()->toArray();

        return response()->json($all_users);
    }

    public function deleteReservation(Request $request)
    {
        $reservation_id = $request->reservation_id;
        
        $this->reservationRepository->delete($reservation_id);

        return response()->json($this->status('reservation deleted!'));
    }

    public function deleteUser(Request $request)
    {
        $user_id = $request->user_id;

        $this->userRepository->deleteOnlyNormalUser($user_id);

        return response()->json($this->status('user deleted!'));
    }

    public function editUser(Request $request)
    {
        $this->userRepository->findAndEdit($request);

        return response()->json($this->status('user edited!'));
    }

    private function status($message)
    {
        return ['status' => $message];
    }
}

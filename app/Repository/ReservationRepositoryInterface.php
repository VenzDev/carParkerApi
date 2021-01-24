<?php

namespace App\Repository;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface ReservationRepositoryInterface
{
    public function delete(int $id);
    public function activeReservations(): Collection;
    public function activeUserReservations(int $user_id): Collection;
    public function carsOnParking(): int;
    public function activeUserReservationBetweenTime(int $user_id, Carbon $time): Collection;
    public function carOnParkingReservation(int $parking_slot_id): Collection;
}

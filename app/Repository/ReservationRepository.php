<?php

namespace App\Repository;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReservationRepository implements ReservationRepositoryInterface
{
    public function delete(int $id)
    {
        Reservation::query()->where('id', $id)->delete();
    }

    public function activeReservations(string $with = null, int $pagination = null): Collection
    {
        $query = Reservation::query()
        ->whereIn('status', ['RESERVED', 'CAR ON PARKING']);

        if($with){
            $query = $query->with($with);
        }

        if($pagination){
            $query = $query->simplePaginate($pagination);
        }

        return $query;
    }

    public function activeUserReservations(int $user_id): Collection
    {
        return Reservation::all()
        ->whereIn('status', ['RESERVED', 'CAR ON PARKING'])
        ->where('user_id', $user_id);
    }

    public function carsOnParking(): int
    {
        return Reservation::where('status', 'CAR ON PARKING')->get()->count();
    }

    public function activeUserReservationBetweenTime(int $user_id, Carbon $time): Collection
    {
        return $this->activeUserReservations($user_id)
        ->filter(function ($item) use (&$time) {
            if ($time->between($item->system_reservation_from, $item->reservation_to)) {
                return $item;
            }
        })
        ->first();
    }

    public function carOnParkingReservation(int $parking_slot_id): Collection
    {
        return Reservation::where('parking_slot_id', $parking_slot_id)
        ->where('status', 'CAR ON PARKING')->first();
    }
}

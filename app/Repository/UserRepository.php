<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function all()
    {
        return User::all();
    }

    public function findById(int $id): Collection
    {
        return User::query()->find($id);
    }

    public function findWithActiveReservations(int $id): Collection
    {
        return User::with(['reservations' => function ($query) {
            $query->whereIn('status', ['RESERVED', 'CAR ON PARKING']);
        }])->where('id', $id)->get()->first();
    }

    public function create(string $name, string $email, string $password)
    {
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'USER',
            'isActive' => false
        ]);
    }

    public function findByRfid(int $id): Collection
    {
        return User::where('rfid_card_id', $id)->first();
    }

    public function findAndEdit(Request $request)
    {
        $finded_user = User::query()->where('id', $request->id)->first();

        $finded_user->name = $request->name;
        $finded_user->email = $request->email;
        $finded_user->rfid_card_id = $request->rfid_card_id;
        $finded_user->is_active = $request->is_active;

        $finded_user->save();
    }

    public function deleteOnlyNormalUser(int $id)
    {
        User::where('id', $id)->where('role', 'USER')->first()->delete();
    }

    public function activeUser(int $user_id)
    {
        $user = $this->findById($user_id);
        $user->is_active = true;
        $user->save();
    }
}

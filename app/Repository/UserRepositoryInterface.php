<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface UserRepositoryInterface
{
    public function all();
    public function create(string $name, string $email, string $password);
    public function findById(int $id): Collection;
    public function findByRfid(int $id): Collection;
    public function findWithActiveReservations(int $id): Collection;
    public function findAndEdit(Request $request);
    public function deleteOnlyNormalUser(int $id);
    public function activeUser(int $user_id);
}

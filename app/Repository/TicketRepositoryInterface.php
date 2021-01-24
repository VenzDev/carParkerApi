<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;

interface TicketRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): Collection;
    public function getByUserId(int $id): Collection;
    public function delete(int $id): void;
    public function create(int $user_id, string $title, string $content): void;
}

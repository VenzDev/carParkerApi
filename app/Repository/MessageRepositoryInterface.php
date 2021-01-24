<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface
{
    public function create(int $ticket_id, int $user_id, string $content): void;
}

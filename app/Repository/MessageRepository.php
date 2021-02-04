<?php

namespace App\Repository;

use App\Models\Message;

class MessageRepository implements MessageRepositoryInterface
{
    public function create(int $ticket_id, int $user_id, string $content): void
    {
        $message = new Message();

        $message->ticket_id = $ticket_id;
        $message->user_id = $user_id;
        $message->content = $content;
        $message->save();
    }
}

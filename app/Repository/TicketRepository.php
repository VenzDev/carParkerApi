<?php

namespace App\Repository;

use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class TicketRepository implements TicketRepositoryInterface
{
    public function getAll(): Collection
    {
        return Ticket::query()
        ->where('is_finished', false)
        ->with('messages.user:id,name,role')
        ->get();
    }

    public function getByUserId(int $id): Collection
    {
        return Ticket::where('is_finished', false)
        ->where('user_id', $id)
        ->with('messages.user:id,name,role')
        ->get()
        ->first();
    }

    public function getById(int $id): Collection
    {
        return Ticket::where('id', $id)
        ->with('messages.user:id,name,role')
        ->get()
        ->first();
    }

    public function delete(int $id): void
    {
         Ticket::where('id', $id)->first()->delete();
    }

    public function create(int $user_id, string $title, string $content): void
    {
        $new_ticket = new Ticket();

        $new_ticket->title = $title;
        $new_ticket->is_finished = false;
        $new_ticket->user_id = $user_id;
        $new_ticket->save();

        $message = new Message();

        $message->ticket_id = $new_ticket->id;
        $message->user_id = $user_id;
        $message->content = $content;
        
        $message->save();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function createTicket(Request $request)
    {
        $user_id = $request->user()->id;
        $new_ticket = new Ticket();

        $new_ticket->title = $request['title'];
        $new_ticket->is_finished = false;
        $new_ticket->user_id = $user_id;
        $new_ticket->save();

        $message = new Message();
        $message->user_id = $user_id;
        $message->content = $request['content'];
        $message->ticket_id = $new_ticket->id;
        $message->save();

        return response()->json(['status' => 'success']);
    }

    public function getTickets()
    {
        $all_tickets = Ticket::query()->where('is_finished', false)->with('messages.user:id,name,role')->get()->toArray();

        return response()->json($all_tickets);
    }

    public function getUserTicket(Request $request)
    {
        $user_id = $request->user()->id;
        $ticket = Ticket::query()->where('is_finished', false)->where('user_id', $user_id)->with('messages.user:id,name,role')->get()->first();

        return response()->json($ticket);
    }

    public function getTicketById(Request $request)
    {
        $id = $request['id'];
        $ticket = Ticket::query()->where('id', $id)->with('messages.user:id,name,role')->get()->first();

        return response()->json($ticket);
    }

    public function addTicketMessage(Request $request)
    {
        $ticket_id = $request['ticket_id'];
        $user_id = $request->user()->id;

        $message = new Message();

        $message->ticket_id = $ticket_id;
        $message->user_id = $user_id;
        $message->content = $request['content'];
        $message->save();

        $ticket = Ticket::query()->where('id', $ticket_id)->with('messages.user:id,name,role')->first();

        return response()->json($ticket);
    }

    public function deleteTicket(Request $request)
    {
        $ticket_id = $request['ticket_id'];
        Ticket::query()->where('id', $ticket_id)->first()->delete();

        return response()->json(['status' => 'success']);
    }

    public function setTicketAsFinished(Request $request)
    {
        $ticket_id = $request['ticket_id'];
        $ticket = Ticket::query()->where('id', $ticket_id)->first();
        $ticket->is_finished = true;
        $ticket->save();

        return response()->json(['status' => 'finished']);
    }
}

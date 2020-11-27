<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function createTicket(Request $request)
    {
        $user_id = $request->user()->id;
        $new_ticket = new Ticket();

        $array = [['USER', $request['message']]];
        
        $new_ticket->title = $request['title'];
        $new_ticket->is_finished = false;
        $new_ticket->user_id = $user_id;
        $new_ticket->messages = serialize($array);

        $new_ticket->save();

        return response()->json(['status' => 'success']);
    }

    public function getTickets()
    {
        $all_tickets = Ticket::query()->where('is_finished',false)->get()->toArray();

        return response()->json($all_tickets);
    }

    public function getUserTicket(Request $request)
    {
        $user_id = $request->user()->id;
        $ticket = Ticket::query()->where('user_id', $user_id)->first();

        if($ticket){
            $ticket->messages = unserialize($ticket->messages);
            return response()->json($ticket);
        }
        else return response()->json(null);

    }

    public function getTicketById(Request $request)
    {
        $id = $request['id'];
        $ticket = Ticket::query()->where('id', $id)->first();

        if($ticket){
            $ticket->messages = unserialize($ticket->messages);
            return response()->json($ticket);
        }
        else return response()->json(null);
    }

    public function addTicketMessage(Request $request)
    {
        $user_id = $request->user()->id;
        $user_role = $request->user()->role;

        $ticket = Ticket::query()
        ->where('user_id', $user_id)
        ->where('is_finished', false)
        ->first();

        $messages = unserialize($ticket->messages);

        if($user_role === 'ADMIN'){
            array_push($messages, ['ADMIN', $request['message']]);
        } else if($user_role === 'USER') {
            array_push($messages, ['USER', $request['message']]);
        }

        $ticket->messages = serialize($messages);
        $ticket->save();

        $ticket->messages = unserialize($ticket->messages);

        return response()->json($ticket);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repository\MessageRepository;
use App\Repository\TicketRepository;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private $ticketRepository;
    private $messageRepository;

    public function __construct(TicketRepository $ticketRepository, MessageRepository $messageRepository)
    {
        $this->ticketRepository = $ticketRepository;
        $this->messageRepository = $messageRepository;
    }

    public function createTicket(Request $request)
    {
        $user_id = $request->user()->id;
        
        $this->ticketRepository->create($user_id, $request->title, $request->content);

        return response()->json(['status' => 'success']);
    }

    public function getTickets()
    {
        $all_tickets = $this->ticketRepository->getAll()->toArray();

        return response()->json($all_tickets);
    }

    public function getUserTicket(Request $request)
    {
        $user_id = $request->user()->id;
        $ticket = $this->ticketRepository->getByUserId($user_id);

        return response()->json($ticket);
    }

    public function getTicketById(Request $request)
    {
        $id = $request->id;
        $ticket = $this->ticketRepository->getById($id);

        return response()->json($ticket);
    }

    public function addTicketMessage(Request $request)
    {
        $ticket_id = $request->ticket_id;
        $user_id = $request->user()->id;

        $this->messageRepository->create($ticket_id, $user_id, $request->content);

        $ticket = $this->ticketRepository->getById($ticket_id);

        return response()->json($ticket);
    }

    public function deleteTicket(Request $request)
    {
        $ticket_id = $request->ticket_id;
        $this->ticketRepository->delete($ticket_id);

        return response()->json(['status' => 'success']);
    }

    public function setTicketAsFinished(Request $request)
    {
        $ticket_id = $request->ticket_id;
        $ticket = $this->ticketRepository->getById($ticket_id);
        if (!$ticket) {
            abort(422, 'Ticket not found');
        }

        $ticket->is_finished = true;
        $ticket->save();

        return response()->json(['status' => 'finished']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class TicketReservationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        $data = $request->validate([
            'number_of_tickets' => ['required', 'min:1']
        ]);

        try {
            $tickets = $request->user()->reserveTickets($event, $data['number_of_tickets']);
        } catch (\App\Exceptions\InsufficientTicketsException $th) {
            abort(400, $th->getMessage());
        } catch (\App\Exceptions\EventBookingClosedException $th) {
            abort(400, $th->getMessage());
        }
        
        return response()->json([
            'tickets' => $tickets
        ], 201);
    }
}

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
            abort(410, $th->getMessage()); // Gone Status Code
        } catch (\App\Exceptions\EventBookingClosedException $th) {
            abort(423, $th->getMessage()); // Locked Status Code
        }
        // Why no catch? because we want this to fail based on our test...
        
        return response()->json([
            'tickets' => $tickets
        ], 201);
    }
}

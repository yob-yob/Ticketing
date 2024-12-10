<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'max:50'],
            'description' => ['required', 'max:255'],
            'datetime' => ['required', 'date', 'after:now'],
            'location' => ['required', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'], // 0 = if it's free
            'attendee_limit' => ['required', 'numeric', 'min:1'],
        ]);

        $event = Event::create($data);

        for ($i = 0; $i < $data['attendee_limit']; $i++) { 
            $event->tickets()->create();
        }

        return response()->json([
            'event' => $event,
        ], 201);
    }
}
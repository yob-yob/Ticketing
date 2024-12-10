<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::query()->withCount('availableTickets')->withAvg('reviews', 'rating')->get();

        // We may want to use Laravel's API Resources here... for better data transformation...
        return response()->json([
            'events' => EventResource::collection($events)
        ]);
    }

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
        $event->createTickets($data['attendee_limit']);

        return response()->json([
            'event' => $event,
        ], 201);
    }
}

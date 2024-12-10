<?php

namespace App\Http\Controllers;

use App\Exceptions\CannotReviewOngoingEventException;
use App\Exceptions\UnauthorizedReviewException;
use App\Models\Event;
use App\Models\Review;
use Illuminate\Http\Request;

class EventReviewController extends Controller
{
    public function index(Event $event)
    {
        $event = $event->loadMissing('reviews')->loadAvg('reviews', 'rating');

        return response()->json([
            'reviews' => $event->reviews,
            'average_rating' => $event->reviews_avg_rating,
        ]);
    }

    public function create(Event $event, Request $request)
    {
        $data = $request->validate([
            'comment' => ['required', 'max:1000'], // max 1000 characters
            'rating' => ['required', 'numeric', 'min:1', 'max:5']
        ]);

        try {
            $review = $request->user()->reviewEvent($event, $data['comment'], $data['rating']);
        } catch (UnauthorizedReviewException $th) {
            abort(403, $th->getMessage());
        } catch (CannotReviewOngoingEventException $th) {
            abort(403, $th->getMessage());
        }
        // Why no catch? because we want this to fail based on our test...
        
        
        return response()->json([
            'review' => $review
        ]);
    }
}

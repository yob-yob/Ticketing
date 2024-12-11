<?php

use App\Models\Event;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(LazilyRefreshDatabase::class);

test('only authenticated users can create a review', function () {
    $event = Event::factory()->create([
        'datetime' => now()->addMinute()
    ]);
    
    $event->createTickets(1);
    
    $user = User::factory()->create();
    
    $user->reserveTickets($event, 1);

    $this->travelTo(now()->addHour()->addMinutes(2));

    $review = [
        'comment' => fake()->sentences(3, true),
        'rating' => 5,
    ];

    $response = $this->postJson(route("event.review.create", ['event' => $event]), $review);

    $response->assertStatus(401);

    $this->assertDatabaseCount('reviews', 0);
});

test('users can leave a review on an event', function () {
    $event = Event::factory()->create([
        'datetime' => now()->addMinute()
    ]);
    
    $event->createTickets(1);
    
    $user = User::factory()->create();
    
    $user->reserveTickets($event, 1);

    Sanctum::actingAs($user);

    $this->travelTo(now()->addHour()->addMinutes(2));

    $review = [
        'comment' => fake()->sentences(3, true),
        'rating' => 5,
    ];

    $response = $this->postJson(route("event.review.create", ['event' => $event]), $review);

    $response->assertStatus(200);

    $this->assertDatabaseCount('reviews', 1);
    $this->assertDatabaseHas('reviews', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        ...$review
    ]);
});

test('create a review validation', function ($input, $validationError) {
    $event = Event::factory()->create();
    $event->createTickets(1);
    
    $user = User::factory()->create();
    $user->reserveTickets($event, 1);

    Sanctum::actingAs($user);

    $review = array_merge([
        'comment' => fake()->sentences(3, true),
        'rating' => 5,
    ], $input);

    $response = $this->postJson(route("event.review.create", ['event' => $event]), $review);

    $response->assertInvalid($validationError);

    $this->assertDatabaseCount('reviews', 0);
})->with([
    'Comment must be provided' => [['comment' => null], ['comment' => "The comment field is required."]],
    'Comment must be <= 1000 characters' => [['comment' => fake()->words(1000, true)], ['comment' => "The comment field must not be greater than 1000 characters."]],
    'Rating must be provided' => [['rating' => null], ['rating' => "The rating field is required."]],
    'Rating must be numeric' => [['rating' => "non-numeric-value"], ['rating' => "The rating field must be a number."]],
    'Rating must be <= 5' => [['rating' => 6], ['rating' => "The rating field must not be greater than 5."]],
    'Rating must be >= 1' => [['rating' => 0], ['rating' => "The rating field must be at least 1."] ]
]);

test('only users that has purchased a ticket for that event can leave a review on that event', function () {
    $event = Event::factory()->create();
    $event->createTickets(1);

    $event2 = Event::factory()->create();
    $event2->createTickets(1);
    
    $user = User::factory()->create();
    $user->reserveTickets($event, 1); // this is to ensure that the user really has an event

    Sanctum::actingAs($user);

    $this->travelTo(now()->addDay()->addHour()->addMinutes(2));

    $review = [
        'comment' => fake()->sentences(3, true),
        'rating' => 5,
    ];

    // User will attempt to create a review on event 2
    $response = $this->postJson(route("event.review.create", ['event' => $event2]), $review);

    $response->assertStatus(403);

    $this->assertDatabaseCount('reviews', 0);
});

test('users can only submit a review after attending an event', function () {
    $event = Event::factory()->create([
        'datetime' => now()->addDay(2) // 2 days into the future...
    ]);
    $event->createTickets(1);

    $user = User::factory()->create();
    $user->reserveTickets($event, 1); // this is to ensure that the user really has an event

    Sanctum::actingAs($user);

    $review = [
        'comment' => fake()->sentences(3, true),
        'rating' => 5,
    ];

    // User will attempt to create a review on event that does not yet finished or is still in the past...
    $response = $this->postJson(route("event.review.create", ['event' => $event]), $review);

    $response->assertStatus(403);

    $this->assertDatabaseCount('reviews', 0);
});

test('can get all reviews for a specific event with average rating', function () {
    $event = Event::factory()->create();
    
    $reviews = Review::factory(5)->create([
        'event_id' => $event->id
    ]);

    Sanctum::actingAs(User::factory()->create());

    $review = [
        'comment' => fake()->sentences(3, true),
        'rating' => 5,
    ];

    // User will attempt to create a review on event 2
    $response = $this->get(route("event.review.index", ['event' => $event]));

    $response->assertStatus(200);

    $response->assertJsonCount(5, 'reviews');

    $response->assertJsonFragment(['average_rating' => $reviews->sum('rating') / $reviews->count()]);
});

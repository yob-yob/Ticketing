<?php

use App\Models\Event;
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

    $response = $this->postJson("/api/event/{$event->id}/review", $review);

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

    $response = $this->postJson("/api/event/{$event->id}/review", $review);

    $response->assertStatus(200);

    $this->assertDatabaseCount('reviews', 1);
    $this->assertDatabaseHas('reviews', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        ...$review
    ]);
});

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
    $response = $this->postJson("/api/event/{$event2->id}/review", $review);

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

    // User will attempt to create a review on event 2
    $response = $this->postJson("/api/event/{$event->id}/review", $review);

    $response->assertStatus(403);

    $this->assertDatabaseCount('reviews', 0);
});
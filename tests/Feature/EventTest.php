<?php

use App\Models\Event;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('users can create event', function () {
    $eventDetails = [
        'title' => 'Test Title',
        'description' => 'Test Description',
        'datetime' => now()->addMinute(),
        'location' => fake()->city(),
        'price' => 100000,
        'attendee_limit' => 5,
    ];

    Sanctum::actingAs(User::factory()->create());

    $response = $this->post('/api/event/create', $eventDetails);

    $response->assertStatus(201);

    unset($eventDetails['attendee_limit']);

    $this->assertDatabaseHas('events', $eventDetails);
    $this->assertDatabaseCount('tickets', 5);
});

test('users must be authenticated to create an event', function () {
    $eventDetails = [
        'title' => 'Test Title',
        'description' => 'Test Description',
        'datetime' => now()->addMinute(),
        'location' => fake()->city(),
        'price' => 100000,
        'attendee_limit' => 5,
    ];

    $response = $this->postJson('/api/event/create', $eventDetails);
    
    $response->assertStatus(401);
    $this->assertDatabaseCount('events', 0);
    $this->assertDatabaseCount('tickets', 0);
});

test('users can get all events', function () {
    Sanctum::actingAs(User::factory()->create());

    $events = Event::factory(10)->create();
    $events->first()->createTickets(5); // first event has 10 tickets

    $response = $this->get('/api/event/index');
    
    $response->assertStatus(200);

    $response->assertJsonCount(10, 'events');

    $response->assertJsonFragment(['available_tickets_count' => 5]);
});
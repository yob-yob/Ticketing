<?php

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
<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('users can create event', function () {
    $eventDetails = [
        'title' => 'Test Title',
        'description' => 'Test Description',
        'datetime' => now(),
        'location' => fake()->city(),
        'price' => 100000,
        'attendee' => 5,
    ];

    Sanctum::actingAs(User::factory()->create());

    $response = $this->post('/api/event/create', $eventDetails);

    $response->assertStatus(201);

    $this->assertDatabaseHas('events', $eventDetails);
});

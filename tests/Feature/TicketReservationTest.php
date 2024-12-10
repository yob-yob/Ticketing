<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(LazilyRefreshDatabase::class);

test('users can reserve a ticket for an event', function () {
    Sanctum::actingAs(User::factory()->create());

    $event = Event::factory()->create();
    $event->createTickets(5);

    $response = $this->post("/api/event/{$event->id}/reserve", [
        'number_of_tickets' => 1
    ]);

    $response->assertStatus(201);

    $this->assertEquals($event->reservedTickets->count(), 1);
});

test('users cannot reserve a ticket from an event that has no available tickets', function () {
    Sanctum::actingAs(User::factory()->create());

    $event = Event::factory()->create();
    $event->createTickets(0);

    $response = $this->post("/api/event/{$event->id}/reserve", [
        'number_of_tickets' => 1
    ]);

    $response->assertStatus(400);

    $this->assertEquals($event->reservedTickets->count(), 0);
});

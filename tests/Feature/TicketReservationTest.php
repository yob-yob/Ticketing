<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(LazilyRefreshDatabase::class);

test('users must be authenticated to reserve a ticket', function () {
    $event = Event::factory()->create();
    $event->createTickets(5);

    $response = $this->postJson("/api/event/{$event->id}/reserve", [
        'number_of_tickets' => 1
    ]);

    $response->assertStatus(401);
});

test('users can reserve a ticket for an event', function () {
    Sanctum::actingAs(User::factory()->create());

    $event = Event::factory()->create();
    $event->createTickets(5);

    $response = $this->post("/api/event/{$event->id}/reserve", [
        'number_of_tickets' => 1
    ]);

    $response->assertStatus(201);

    $this->assertEquals($event->availableTickets->count(), 4);
    $this->assertEquals($event->reservedTickets->count(), 1);
});

test('users can reserve MULTIPLE tickets for an event', function () {
    Sanctum::actingAs(User::factory()->create());

    $event = Event::factory()->create();
    $event->createTickets(5);

    $response = $this->post("/api/event/{$event->id}/reserve", [
        'number_of_tickets' => 5
    ]);

    $response->assertStatus(201);

    $this->assertEquals($event->availableTickets->count(), 0);
    $this->assertEquals($event->reservedTickets->count(), 5);
});

test('ticket reservation validation', function ($input, $validationError) {
    Sanctum::actingAs(User::factory()->create());

    $event = Event::factory()->create();
    $event->createTickets(5);

    $response = $this->post("/api/event/{$event->id}/reserve", $input);

    $response->assertInvalid($validationError);

    $this->assertEquals($event->availableTickets->count(), 5);
    $this->assertEquals($event->reservedTickets->count(), 0);
})->with([
    'number of tickers is required' => [
        ['number_of_tickets' => null], ['number_of_tickets' => "The number of tickets field is required."]
    ],
    'number of tickers must be numeric' => [
        ['number_of_tickets' => "non-numeric-valie"], ['number_of_tickets' => "The number of tickets field must be a number."]
    ],
    'number of tickers must be greater than 1' => [
        ['number_of_tickets' => 0], ['number_of_tickets' => "The number of tickets field must be at least 1."]
    ]
]);

test('users cannot reserve a ticket from an event that has no available tickets', function () {
    Sanctum::actingAs(User::factory()->create());

    $event = Event::factory()->create();
    $event->createTickets(0);

    $response = $this->post("/api/event/{$event->id}/reserve", [
        'number_of_tickets' => 1
    ]);

    $response->assertStatus(410);

    $this->assertEquals($event->reservedTickets->count(), 0);
});

test('users cannot reserve a ticket from an event where all tickets has already been reserved', function () {
    Sanctum::actingAs($user = User::factory()->create());

    $event = Event::factory()->create();
    $event->createTickets(5);
    $event->availableTickets()->update([
        'user_id' => User::factory()->create()->id
    ]);

    $response = $this->post("/api/event/{$event->id}/reserve", [
        'number_of_tickets' => 1
    ]);

    $response->assertStatus(410);
    
    $this->assertEquals($event->availableTickets->count(), 0);
    $this->assertEquals($event->reservedTickets->count(), 5);
    $this->assertEquals($user->tickets->count(), 0);
});

test('users cannot reserve a ticket from an event where the deadline has passed', function () {
    Sanctum::actingAs(User::factory()->create());

    $event = Event::factory()->create([
        'datetime' => now()->subSecond() // Subtract 1 second to NOW() -> this ensures that the validation affects instantly
    ]);
    $event->createTickets(5); // Ensure that there are still available tickets to book for this event. ()

    $response = $this->post("/api/event/{$event->id}/reserve", [
        'number_of_tickets' => 1
    ]);

    $response->assertStatus(423);

    $this->assertEquals($event->reservedTickets->count(), 0);
});

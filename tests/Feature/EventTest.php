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

test('create event validation', function ($input, $validationError) {
    $eventDetails = array_merge([
        'title' => 'Test Title',
        'description' => 'Test Description',
        'datetime' => now()->addDay(),
        'location' => fake()->city(),
        'price' => 100000,
        'attendee_limit' => 5,
    ], $input);

    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/event/create', $eventDetails);

    $response->assertInvalid($validationError);

    $this->assertDatabaseCount('events', 0);
    $this->assertDatabaseCount('tickets', 0);
})->with([
    'title must be provided' => [['title' => null], ['title' => 'The title field is required.']],
    'description must be provided' => [['description' => null], ['description' => 'The description field is required.']],
    'datetime must be provided' => [['datetime' => null], ['datetime' => 'The datetime field is required.']],
    'location must be provided' => [['location' => null], ['location' => 'The location field is required.']],
    'price must be provided' => [['price' => null], ['price' => 'The price field is required.']],
    'attendee_limit must be provided' => [['attendee_limit' => null], ['attendee_limit' => 'The attendee limit field is required.']],
    'datetime must be in the future' => [['datetime' => now()->subMinute()], ['datetime' => 'The datetime field must be a date after now.']],
    'price must be numeric' => [['price' => 'non-numeric-value'], ['price' => 'The price field must be a number.']],
    'attendee limit must be numeric' => [['attendee_limit' => 'non-numeric-value'], ['attendee_limit' => 'The attendee limit field must be a number.']],
    'attendee limit must be greater than 1' => [['attendee_limit' => 0], ['attendee_limit' => 'The attendee limit field must be at least 1.']]
]);

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
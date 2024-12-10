<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('users can make a book a ticket for an event reservation', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

<?php

use App\Models\User;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('users can register', function () {
    $response = $this->post('/register', [
        'name' => "test",
        'email' => 'test@example.com',
        'password' => '123qwe123',
        'password_confirmation' => '123qwe123'
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('duplicate email is not allowed', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post('/register', [
        'name' => "test",
        'email' => 'test@example.com',
        'password' => '123qwe123',
        'password_confirmation' => '123qwe123'
    ]);

    $response->assertInvalid([
        'email' => 'The email has already been taken.'
    ]);
});

test('users can login', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => '123qwe123',
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => '123qwe123',
        'device_name' => 'testing',
    ]);

    $response->assertJsonStructure(['token']);
});

test('invalid credentials are handled', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => '1234567890',
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => '0123456789',
        'device_name' => 'testing',
    ]);

    $response->assertInvalid();
});

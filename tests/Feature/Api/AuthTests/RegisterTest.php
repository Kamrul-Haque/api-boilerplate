<?php

use App\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'user', 'display_name' => 'User']);

    $this->userData = [
        'name' => 'Mr. Admin',
        'email' => 'admin@email.com',
        'password' => 'Password@123',
        'password_confirmation' => 'Password@123',
    ];
});

test('register endpoint works', function () {
    $response = $this->postJson('/api/register');

    $response->assertStatus(422);
});

test('register validation works', function () {
    $this->postJson('/api/register')->assertStatus(422);
    $this->postJson('/api/register', [
        'name' => 'admin #1',
        'email' => 'admin%email.com',
        'password' => 'test',
    ])
         ->assertStatus(422)
         ->assertJsonStructure([
             'message',
             'errors' => ['name', 'email', 'password'],
         ]);
    $this->postJson('/api/register', $this->userData)->assertStatus(200);
    $this->postJson('/api/register', $this->userData)->assertStatus(422);
});

test('register response is correct', function () {
    $this->postJson('/api/register', $this->userData)
         ->assertStatus(200)
         ->assertJsonStructure([
             'message',
             'token',
             'user' => ['id', 'name', 'email', 'phone', 'avatar'],
         ]);
});

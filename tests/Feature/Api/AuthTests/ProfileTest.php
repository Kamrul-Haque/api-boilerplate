<?php

use App\Models\User;

beforeEach(function () {
    $this->password = 'Password@12345';

    $this->user = User::factory()->create([
        'name' => 'admin',
        'email' => 'admin@email.com',
        'password' => $this->password,
    ]);
});

test('get profile endpoint works and requires authentication', function () {
    $this->getJson('/api/profile')
        ->assertStatus(401);
});

test('get profile endpoint response format is correct', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/profile')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'phone', 'avatar'],
        ]);
});

test('put profile endpoint works and requires authentication', function () {
    $this->putJson('/api/profile')
        ->assertStatus(401);
});

test('put profile validation works', function () {
    $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/profile')
        ->assertStatus(422);

    $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/profile', [
            'email' => 'admin%email.com',
            'password' => 'test',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['name', 'email'],
        ]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/profile', $this->user->toArray())->assertStatus(200);
});

test('put profile endpoint response format is correct', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/profile')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'phone', 'avatar'],
        ]);
});

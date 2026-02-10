<?php

use App\Models\User;

beforeEach(function () {
    $this->password = 'Pa$$word';

    $this->user = User::create([
        'name' => 'test',
        'email' => 'test@test.com',
        'password' => $this->password,
    ]);
});

test('update password endpoint works and requires authentication', function () {
    $this->putJson('/api/update-password')
         ->assertStatus(401);
});

test('update password validation works', function () {
    $this->actingAs($this->user, 'sanctum')
         ->putJson('/api/update-password')
         ->assertStatus(422);

    $this->actingAs($this->user, 'sanctum')
         ->putJson('/api/update-password', ['password' => 'test'])
         ->assertStatus(422);

    $this->actingAs($this->user, 'sanctum')
         ->putJson('/api/update-password', ['password' => 'password', 'password_confirmation' => 'password1'])
         ->assertStatus(422);

    $this->actingAs($this->user, 'sanctum')
         ->putJson(
             '/api/update-password',
             ['password' => 'password', 'password_confirmation' => 'password', 'current_password' => 'password']
         )
         ->assertStatus(422);
});

test('update password works', function () {
    $this->actingAs($this->user, 'sanctum')
         ->putJson(
             '/api/update-password',
             ['current_password' => $this->password, 'password' => 'password', 'password_confirmation' => 'password']
         )
         ->assertStatus(200)
         ->assertJsonStructure(['message']);
});

<?php

use App\Models\User;

test('logout endpoint works and requires authentication', function () {
    $this->postJson('/api/logout')
         ->assertStatus(401);
});

test('logout works', function () {
    $user = User::factory()->create([
        'name' => 'Mr. Admin',
        'email' => 'admin@email.com',
        'password' => 'Password@123',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $this->postJson('/api/logout', [], ['Authorization' => 'Bearer '.$token])
         ->assertStatus(200);
    $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
});

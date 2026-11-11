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

    $login = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'Password@123',
    ])->assertStatus(200);

    $accessToken = $login->json('access_token');
    $refreshToken = $login->json('refresh_token');

    $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->postJson('/api/logout')
        ->assertStatus(200);

    $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);

    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->getJson('/api/profile')
        ->assertStatus(401);

    $this->postJson('/api/refresh-token', ['refresh_token' => $refreshToken])
        ->assertStatus(401);
});

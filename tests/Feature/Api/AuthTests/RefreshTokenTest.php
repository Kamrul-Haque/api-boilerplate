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

test('refresh endpoint returns a new token pair', function () {
    $login = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(200);

    $oldAccessToken = $login->json('access_token');

    $this->withHeader('Authorization', 'Bearer '.$oldAccessToken)
        ->getJson('/api/profile')
        ->assertStatus(200);

    $response = $this->postJson('/api/refresh-token', [
        'refresh_token' => $login->json('refresh_token'),
    ])->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'token',
            'access_token',
            'refresh_token',
            'token_type',
            'expires_in',
            'user' => ['id', 'name', 'email'],
        ]);

    expect($response->json('access_token'))->not->toBe($login->json('access_token'));
    expect($response->json('refresh_token'))->not->toBe($login->json('refresh_token'));

    $this->withHeader('Authorization', 'Bearer '.$response->json('access_token'))
        ->getJson('/api/profile')
        ->assertStatus(200);

    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$oldAccessToken)
        ->getJson('/api/profile')
        ->assertStatus(401);
});

test('refresh token cannot be used as a bearer token', function () {
    $login = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(200);

    $this->withHeader('Authorization', 'Bearer '.$login->json('refresh_token'))
        ->getJson('/api/profile')
        ->assertStatus(401);
});

test('reusing a rotated refresh token revokes the session', function () {
    $login = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(200);

    $rotated = $this->postJson('/api/refresh-token', [
        'refresh_token' => $login->json('refresh_token'),
    ])->assertStatus(200);

    $newAccessToken = $rotated->json('access_token');

    $this->withHeader('Authorization', 'Bearer '.$newAccessToken)
        ->getJson('/api/profile')
        ->assertStatus(200);

    $this->postJson('/api/refresh-token', [
        'refresh_token' => $login->json('refresh_token'),
    ])->assertStatus(401);

    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$newAccessToken)
        ->getJson('/api/profile')
        ->assertStatus(401);
});

test('invalid refresh token is rejected', function () {
    $this->postJson('/api/refresh-token')
        ->assertStatus(422);

    $this->postJson('/api/refresh-token', ['refresh_token' => 'invalid-token'])
        ->assertStatus(401);
});

<?php

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->password = 'Password@12345';

    $this->user = User::factory()->create([
        'name' => 'admin',
        'email' => 'admin@email.com',
        'password' => $this->password,
    ]);

    Artisan::call('cache:clear');
});

test('login endpoint works', function () {
    $response = $this->postJson('/api/login');

    $response->assertStatus(422);
});

test('login validation works', function () {
    $this->postJson('/api/login')->assertStatus(422);
    $this->postJson('/api/login', [
        'email' => 'admin*123',
        'password' => 'test',
    ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['email', 'password'],
        ]);
});

test('credential check works properly', function () {
    $this->postJson('/api/login', ['email' => $this->user->email, 'password' => 'Password#123'])->assertStatus(401);
    $this->postJson('/api/login', ['email' => 'test123@email.com', 'password' => $this->password])->assertStatus(401);
    $this->postJson('/api/login', ['email' => $this->user->email, 'password' => $this->password])->assertStatus(200);
});

test('login response is correct', function () {
    $this->postJson('/api/login', ['email' => $this->user->email, 'password' => $this->password])
        ->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'access_token',
            'refresh_token',
            'expires_in',
            'user' => ['id', 'name', 'email', 'phone', 'avatar'],
        ])
        ->assertJsonPath('expires_in', 3600)
        ->assertJsonMissingPath('data');
});

test('access token verification is cached', function () {
    $login = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(200);

    $accessToken = $login->json('access_token');
    $tokenId = PersonalAccessToken::idFromToken($accessToken);

    $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->getJson('/api/profile')
        ->assertStatus(200);

    expect(Cache::has(PersonalAccessToken::cacheKeyForId($tokenId)))->toBeTrue();

    $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->postJson('/api/logout')
        ->assertStatus(200);

    expect(Cache::has(PersonalAccessToken::cacheKeyForId($tokenId)))->toBeFalse();
});

test('legacy plain tokens are authenticated without a hashed cache key', function () {
    $login = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(200);

    [, $plainToken] = explode('|', $login->json('access_token'), 2);

    $this->withHeader('Authorization', 'Bearer '.$plainToken)
        ->getJson('/api/profile')
        ->assertStatus(200);

    expect(Cache::has(config('token.cache_prefix').hash('sha256', $plainToken)))->toBeFalse();
});

test('access token plain value must match the cached row', function () {
    $login = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(200);

    $accessToken = $login->json('access_token');

    [$id] = explode('|', $accessToken, 2);

    $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->getJson('/api/profile')
        ->assertStatus(200);

    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$id.'|tampered')
        ->getJson('/api/profile')
        ->assertStatus(401);
});

test('rate limit works properly', function () {
    Artisan::call('cache:clear');

    $this->postJson('/api/login')
        ->assertHeader('X-Ratelimit-Limit', 5)
        ->assertHeader('X-Ratelimit-Remaining', 4);

    Artisan::call('cache:clear');

    foreach (range(1, 5) as $limit) {
        $this->postJson('/api/login')->assertStatus(422);
    }

    $this->postJson('/api/login')->assertStatus(429);
});

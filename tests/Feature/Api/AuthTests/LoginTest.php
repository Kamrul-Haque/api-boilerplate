<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

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
             'token',
             'user' => ['id', 'name', 'email', 'phone', 'avatar'],
         ]);
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

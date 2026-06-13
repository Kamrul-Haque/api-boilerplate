<?php

namespace Tests\Feature\Api\UserTests;

use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(
    /**
     * @throws Exception
     */
    function () {
        $this->setUpAccessControl();
    });

test('unauthenticated user cannot access create endpoint', function () {
    $this->postJson('/api/users')
        ->assertStatus(401);
});

test('unauthorized user cannot access create endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/users')
        ->assertStatus(403);
});

test('authenticated user gets validation error for empty request on create endpoint', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/users')
        ->assertStatus(422);
});

test('authenticated user gets validation error for invalid data on create endpoint', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'admin ~1',
            'email' => 'admin%email.com',
            'password' => 'test',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['name', 'email'],
        ]);
});

test('authenticated user can create a user', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'new user',
            'email' => 'newuser@test.com',
            'phone' => '023-4902-3598',
            'roles' => [1, 2],
        ])
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'phone', 'avatar'],
        ]);
});

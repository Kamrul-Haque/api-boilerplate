<?php

namespace Tests\Feature\Api\UserTests;

use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->setUpAccessControl();
});

test('unauthenticated user cannot access update endpoint', function () {
    $this->putJson("/api/users/{$this->user->id}")
         ->assertStatus(401);
});

test('unauthorized user cannot access update endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
         ->putJson("/api/users/{$this->user->id}")
         ->assertStatus(403);
});

test('authenticated user gets validation error for empty request on update endpoint', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->putJson("/api/users/{$this->user->id}", [])
         ->assertStatus(422);
});

test('authenticated user can update a user', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->putJson("/api/users/{$this->user->id}", [
             'name' => 'new user updated',
             'email' => 'new-user@test.com',
             'phone' => '090-1234-5678',
         ])
         ->assertStatus(200)
         ->assertJsonStructure([
             'data' => ['id', 'name', 'email', 'phone', 'avatar'],
         ]);
});

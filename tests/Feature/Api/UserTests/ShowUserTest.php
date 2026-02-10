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

test('unauthenticated user cannot access show endpoint', function () {
    $this->getJson("/api/users/{$this->user->id}")
         ->assertStatus(401);
});

test('unauthorized user cannot access show endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
         ->getJson("/api/users/{$this->user->id}")
         ->assertStatus(403);
});

test('authenticated user can access show endpoint', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->getJson("/api/users/{$this->user->id}")
         ->assertStatus(200);
});

test('response format for show endpoint is correct', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->getJson("/api/users/{$this->user->id}")
         ->assertStatus(200)
         ->assertJsonStructure([
             'data' => ['id', 'name', 'email', 'phone', 'avatar'],
         ]);
});

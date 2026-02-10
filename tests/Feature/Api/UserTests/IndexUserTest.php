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

test('unauthenticated user cannot access index endpoint', function () {
    $this->getJson('/api/users')
         ->assertStatus(401);
});

test('unauthorized user cannot access index endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
         ->getJson('/api/users')
         ->assertStatus(403);
});

test('authenticated user can access index endpoint', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->getJson('/api/users')
         ->assertStatus(200);
});

test('response format for index endpoint is correct', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->getJson('/api/users')
         ->assertStatus(200)
         ->assertJsonStructure([
             'data' => [
                 '*' => ['id', 'name', 'email', 'phone', 'avatar'],
             ],
             'filters' => ['role', 'search', 'sortBy', 'sortDesc', 'perPage'],
         ]);
});

test('user search for index endpoint is working', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->getJson('/api/users?search='.urlencode($this->user->name))
         ->assertStatus(200)
         ->assertJsonFragment(['name' => $this->user->name]);
});

<?php

namespace Tests\Feature\Api\RoleTests;

use App\Models\Role;
use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->setUpAccessControl();

    Role::create([
        'name' => 'test-role',
        'display_name' => 'Test Role',
    ]);
});

test('unauthenticated user cannot access create endpoint', function () {
    $this->postJson('/api/roles')
        ->assertStatus(401);
});

test('unauthorized user cannot access create endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/roles')
        ->assertStatus(403);
});

test('authenticated user gets validation error for empty request', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/roles')
        ->assertStatus(422);
});

test('authenticated user gets validation error for duplicate display name', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/roles', [
            'display_name' => 'Test Role',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['name'],
        ]);
});

test('authenticated user can create a role', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/roles', [
            'display_name' => 'New Role',
            'permissions' => [1, 2, 3],
        ])
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'display_name',
                'is_reserved',
                'permissions',
            ],
        ]);
});

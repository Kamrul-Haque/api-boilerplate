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

    $this->superAdmin = Role::where('name', 'super-admin')->first()->users()->first();

    $this->role = Role::create([
        'name' => 'test-role',
        'display_name' => 'Test Role',
    ]);
});

test('unauthenticated user cannot access update endpoint', function () {
    $this->putJson("/api/roles/{$this->role->id}")
         ->assertStatus(401);
});

test('unauthorized user cannot access update endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
         ->putJson("/api/roles/{$this->role->id}")
         ->assertStatus(403);
});

test('authenticated user gets validation error for empty request', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->putJson("/api/roles/{$this->role->id}", [])
         ->assertStatus(422);
});

test('authenticated user can update a role', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->putJson("/api/roles/{$this->role->id}", [
             'display_name' => 'New Role Updated',
             'permissions' => [1, 2, 3, 4, 5],
         ])
         ->assertStatus(200)
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

test('reserved role can not be updated', function () {
    $role = Role::create([
        'display_name' => 'Reserved Role',
        'name' => 'reserved-role',
        'is_reserved' => 1,
    ]);

    $this->actingAs($this->superAdmin, 'sanctum')
         ->putJson("/api/roles/{$role->id}", [
             'display_name' => 'Reserved Role Updated',
             'permissions' => [1, 2, 3, 4, 5],
         ])
         ->assertStatus(400);
});

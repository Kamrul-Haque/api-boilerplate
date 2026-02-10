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

    $this->role = Role::create([
        'name' => 'test-role',
        'display_name' => 'Test Role',
    ]);

    $this->reservedRole = Role::create([
        'display_name' => 'Reserved Role',
        'name' => 'reserved-role',
        'is_reserved' => 1,
    ]);
});

test('unauthenticated user cannot access delete endpoint', function () {
    $this->deleteJson("/api/roles/{$this->role->id}")
         ->assertStatus(401);
});

test('unauthorized user cannot delete a role', function () {
    $this->actingAs($this->user, 'sanctum')
         ->deleteJson("/api/roles/{$this->role->id}")
         ->assertStatus(403);
});

test('authenticated user can delete a role', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->deleteJson("/api/roles/{$this->role->id}")
         ->assertStatus(200);
});

test('authenticated user cannot delete a reserved role', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->deleteJson("/api/roles/{$this->reservedRole->id}")
         ->assertStatus(400);
});

test('unauthorized user cannot delete a reserved role', function () {
    $this->actingAs($this->user, 'sanctum')
         ->deleteJson("/api/roles/{$this->reservedRole->id}")
         ->assertStatus(403);
});

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
});

test('unauthenticated user cannot access index endpoint', function () {
    $this->getJson('/api/roles')
        ->assertStatus(401);
});

test('unauthorized user cannot access index endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/roles')
        ->assertStatus(403);
});

test('authenticated user can access index endpoint and response format is correct', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->getJson('/api/roles?search='.urlencode($this->role->display_name))
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'display_name', 'is_reserved',
                ],
            ],
            'filters' => ['search', 'sortBy', 'sortDesc', 'perPage'],
        ]);
});

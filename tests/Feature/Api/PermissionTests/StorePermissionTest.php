<?php

namespace Tests\Feature\Api\PermissionTests;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(
    /**
     * @throws Exception
     */
    function () {
        $this->setUpAccessControl();

        $this->admin = Role::where('name', 'admin')->first()->users()->first();

        $this->module = Module::create([
            'name' => 'test-module',
            'display_name' => 'Test Module',
            'route_prefix' => 'test-module',
        ]);
    }
);

test('unauthenticated user cannot access create endpoint', function () {
    $this->postJson('/api/permissions')
        ->assertStatus(401);
});

test('unauthorized user cannot access create endpoint', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/permissions')
        ->assertStatus(403);
});

test('create endpoint returns validation error for empty request', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/permissions')
        ->assertStatus(422);
});

test('create endpoint returns validation error for invalid display name', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/permissions', [
            'name' => 'test ~123',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['name', 'display_name'],
        ]);
});

test('create endpoint returns validation error for existing name', function () {
    Permission::create([
        'module_id' => $this->module->id,
        'name' => 'test-permission',
        'display_name' => 'Test Permission',
    ]);

    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/permissions', [
            'name' => 'test-permission',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['name'],
        ]);
});

test('create endpoint works with valid data and response format is correct', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/permissions', [
            'module_id' => $this->module->id,
            'display_name' => 'New Permission',
            'name' => 'new-permission',
        ])
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'display_name',
                'module',
            ],
        ]);
});

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

        $this->permission = Permission::create([
            'module_id' => $this->module->id,
            'name' => 'test-permission',
            'display_name' => 'Test Permission',
        ]);
    }
);

test('unauthenticated user cannot access update endpoint', function () {
    $this->putJson("/api/permissions/{$this->permission->id}")
        ->assertStatus(401);
});

test('unauthorized user cannot access update endpoint', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/permissions/{$this->permission->id}")
        ->assertStatus(403);
});

test('update endpoint returns validation error for empty request', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->putJson("/api/permissions/{$this->permission->id}")
        ->assertStatus(422);
});

test('update endpoint works with valid data and response format is correct', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->putJson("/api/permissions/{$this->permission->id}", [
            'module_id' => $this->module->id,
            'display_name' => 'New Permission Updated',
            'name' => 'new-permission-updated',
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'display_name',
                'module',
            ],
        ]);
});

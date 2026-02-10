<?php

namespace Tests\Feature\Api\ModuleTests;

use App\Models\Module;
use App\Models\Role;
use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->setUpAccessControl();
    $this->admin = Role::where('name', 'admin')->first()->users()->first();
    $this->module = Module::create([
        'name' => 'test-module',
        'display_name' => 'Test Module',
        'route_prefix' => 'test-prefix',
    ]);
});

test('unauthenticated user cannot access update endpoint', function () {
    $this->putJson("/api/modules/{$this->module->id}")
        ->assertStatus(401);
});

test('unauthorized user cannot access update endpoint', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/modules/{$this->module->id}")
        ->assertStatus(403);
});

test('update endpoint validation fails with invalid data', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->putJson("/api/modules/{$this->module->id}", [])
        ->assertStatus(422);
});

test('update endpoint works and response format is correct', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->putJson("/api/modules/{$this->module->id}", [
            'name' => 'New Role Updated',
            'route_prefix' => 'test-prefix',
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'route_prefix',
                'permissions',
            ],
        ]);
});

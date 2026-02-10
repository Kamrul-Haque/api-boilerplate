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
});

test('unauthenticated user cannot access create endpoint', function () {
    $this->postJson('/api/modules')
        ->assertStatus(401);
});

test('unauthorized user cannot access create endpoint', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/modules')
        ->assertStatus(403);
});

test('create endpoint validation fails with no data', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/modules')
        ->assertStatus(422);
});

test('create endpoint validation fails with invalid data', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/modules', [
            'route_prefix' => 'test ~123',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['name', 'route_prefix'],
        ]);
});

test('create endpoint validation fails with duplicate route_prefix', function () {
    Module::create([
        'name' => 'existing-module',
        'display_name' => 'Existing Module',
        'route_prefix' => 'test-prefix',
    ]);

    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/modules', [
            'name' => 'New Module',
            'route_prefix' => 'test-prefix',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['route_prefix'],
        ]);
});

test('create endpoint works and response format is correct', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson('/api/modules', [
            'name' => 'New Module',
            'route_prefix' => 'new-module',
        ])
        ->assertStatus(201)
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

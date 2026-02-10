<?php

namespace Tests\Feature\Api\ModuleTests;

use App\Models\Module;
use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->setUpAccessControl();

    $this->module = Module::create([
        'name' => 'test-module',
        'display_name' => 'Test Module',
        'route_prefix' => 'test-prefix',
    ]);
});

test('unauthenticated user cannot access index endpoint', function () {
    $this->getJson('/api/modules')
         ->assertStatus(401);
});

test('unauthorized user cannot access index endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
         ->getJson('/api/modules')
         ->assertStatus(403);
});

test('authenticated user can access index endpoint and response format is correct', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
         ->getJson('/api/modules?search='.urlencode($this->module->name))
         ->assertStatus(200)
         ->assertJsonStructure([
             'data' => [
                 '*' => [
                     'id',
                     'name',
                     'description',
                     'route_prefix',
                     'permissions',
                 ],
             ],
             'filters' => ['search', 'sortBy', 'sortDesc', 'perPage'],
         ]);
});

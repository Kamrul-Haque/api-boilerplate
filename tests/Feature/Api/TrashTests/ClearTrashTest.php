<?php

namespace Tests\Feature\Api\TrashTests;

use App\Models\Role;
use App\Models\Trash;
use App\Models\User;
use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->setUpAccessControl();
    $this->admin = Role::where('name', 'admin')->first()?->users()->first();

    // Create some trashed items
    User::factory(3)->create()->each->delete();
});

test('unauthenticated user cannot access trash clear endpoint', function () {
    $this->deleteJson('/api/trashes/clear')
        ->assertStatus(401);
});

test('unauthorized user cannot access trash clear endpoint', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->deleteJson('/api/trashes/clear')
        ->assertStatus(403);
});

test('authenticated user can access trash clear endpoint and response has correct structure', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->deleteJson('/api/trashes/clear')
        ->assertStatus(200)
        ->assertJsonStructure(['message']);
});

test('trash clear endpoint actually clears the trash', function () {
    $this->assertNotEquals(0, Trash::count());

    $this->actingAs($this->systemAdmin, 'sanctum')
        ->deleteJson('/api/trashes/clear');

    $this->assertEquals(0, Trash::count());
});

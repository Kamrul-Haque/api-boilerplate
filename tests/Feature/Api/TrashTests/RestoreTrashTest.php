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

    User::factory()->create()->delete();
    $this->trash = Trash::first();
});

test('unauthenticated user cannot access trash restore endpoint', function () {
    $this->postJson("/api/trashes/{$this->trash->id}/restore")
        ->assertStatus(401);
});

test('unauthorized user cannot access trash restore endpoint', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/trashes/{$this->trash->id}/restore")
        ->assertStatus(403);
});

test('authenticated user can access trash restore endpoint and response has correct structure', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->postJson("/api/trashes/{$this->trash->id}/restore")
        ->assertStatus(200)
        ->assertJsonStructure(['message']);
});

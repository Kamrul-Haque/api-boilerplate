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

test('unauthenticated user cannot access trash delete endpoint', function () {
    $this->deleteJson("/api/trashes/{$this->trash->id}/delete")
        ->assertStatus(401);
});

test('unauthorized user cannot access trash delete endpoint', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->deleteJson("/api/trashes/{$this->trash->id}/delete")
        ->assertStatus(403);
});

test('authenticated user can access trash delete endpoint and response has correct structure', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->deleteJson("/api/trashes/{$this->trash->id}/delete")
        ->assertStatus(200)
        ->assertJsonStructure(['message']);
});

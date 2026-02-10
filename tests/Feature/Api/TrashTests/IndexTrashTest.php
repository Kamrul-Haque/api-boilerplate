<?php

namespace Tests\Feature\Api\TrashTests;

use App\Models\Role;
use App\Models\User;
use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->setUpAccessControl();
    $this->admin = Role::where('name', 'admin')->first()?->users()->first();

    User::factory(5)->create();
    User::whereNot('email', 'LIKE', '%admin%')->inRandomOrder()->first()?->delete();
});

test('unauthenticated user cannot access trash index endpoint', function () {
    $this->getJson('/api/trashes')
        ->assertStatus(401);
});

test('unauthorized user cannot access trash index endpoint', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/trashes')
        ->assertStatus(403);
});

test('authenticated user can access trash index endpoint and response has correct structure', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->getJson('/api/trashes')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'trashable_type', 'trashable_id', 'data', 'deleted_by', 'deleted_at'],
            ],
        ]);
});

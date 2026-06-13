<?php

namespace Tests\Feature\Api\UserTests;

use App\Traits\HasAccessControlSetup;
use Exception;

uses(HasAccessControlSetup::class);

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->setUpAccessControl();
});

test('unauthenticated user cannot access delete endpoint', function () {
    $this->deleteJson("/api/users/{$this->user->id}")
        ->assertStatus(401);
});

test('unauthorized user cannot access delete endpoint', function () {
    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/users/{$this->user->id}")
        ->assertStatus(403);
});

test('authenticated user can delete a user', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->deleteJson("/api/users/{$this->user->id}")
        ->assertStatus(200);
});

test('user cannot delete himself', function () {
    $this->actingAs($this->systemAdmin, 'sanctum')
        ->deleteJson("/api/users/{$this->systemAdmin->id}")
        ->assertStatus(400);
});

<?php

use App\Models\User;
use App\Services\AccessControlService;

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->user = User::create([
        'name' => 'test',
        'email' => 'test@test.com',
        'password' => 'hashed',
    ]);

    AccessControlService::truncateAndCreateDefaultRolesAndAssignPermissions();
    $this->user->assignRole('admin');
    $this->user->assignRole('super-admin');
});

test('switch role endpoint authentication works', function () {
    $this->putJson('/api/switch-role')
         ->assertStatus(401);

    $this->actingAs($this->user, 'sanctum')
         ->putJson('/api/switch-role')
         ->assertStatus(422);
});

test('switch role endpoint validation works', function () {
    $this->actingAs($this->user, 'sanctum')
         ->putJson('/api/switch-role')
         ->assertStatus(422);

    $this->actingAs($this->user, 'sanctum')
         ->putJson('/api/switch-role', ['role_id' => 1])
         ->assertStatus(422);

    $this->actingAs($this->user, 'sanctum')
         ->putJson('/api/switch-role', ['role_id' => 3])
         ->assertStatus(422);
});

test('switch role endpoint works properly', function () {
    $this->actingAs($this->user, 'sanctum')
         ->putJson('/api/switch-role', ['role_id' => 2])
         ->assertStatus(200)
         ->assertJsonStructure([
             'message',
             'user' => [
                 'id', 'name', 'email', 'phone', 'avatar', 'active_role', 'roles', 'permissions',
             ],
         ]);

    $this->assertEquals($this->user->hasRole('super-admin'), true);
});

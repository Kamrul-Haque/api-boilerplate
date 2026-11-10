<?php

use App\Enums\ReservedRole;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

uses(RefreshDatabase::class);

test('access control service can be resolved from container with dependencies injected', function () {
    $service = app(AccessControlService::class);

    expect($service)->toBeInstanceOf(AccessControlService::class);
    expect($service->getModules())->toBeArray()->not->toBeEmpty();
    expect($service->getBasePermissions())->toBeArray()->not->toBeEmpty();
});

test('access control service can seed modules, roles, and users', function () {
    /** @var AccessControlService $service */
    $service = app(AccessControlService::class);

    $service->truncateAndCreateDefaultModulesWithPermissions();
    expect(Module::count())->toBeGreaterThan(0);

    $service->truncateAndCreateDefaultRolesAndAssignPermissions();
    expect(Role::count())->toBeGreaterThan(0);

    $service->truncateAndCreateDefaultUsersAndAssignRoles();
    expect(User::count())->toBeGreaterThan(0);
});

test('authorizeOwnerOrPermission allows system admin', function () {
    /** @var AccessControlService $service */
    $service = app(AccessControlService::class);
    $service->truncateAndCreateDefaultModulesWithPermissions();
    $service->truncateAndCreateDefaultRolesAndAssignPermissions();
    $service->truncateAndCreateDefaultUsersAndAssignRoles();

    $admin = User::first();
    $admin->active_role_id = ReservedRole::SYSTEM_ADMIN->value;
    $this->actingAs($admin, 'sanctum');

    $dummyModel = (object) ['created_by_id' => 9999];

    expect(fn () => $service->authorizeOwnerOrPermission($dummyModel))->not->toThrow(AccessDeniedHttpException::class);
});

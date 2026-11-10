<?php

namespace App\Traits;

use App\Enums\ReservedRole;
use App\Models\Role;
use App\Models\User;
use App\Services\AccessControlService;
use Exception;

trait HasAccessControlSetup
{
    protected object $systemAdmin;

    protected object $user;

    /**
     * @throws Exception
     */
    protected function setUpAccessControl(): void
    {
        $accessControlService = app(AccessControlService::class);
        $accessControlService->truncateAndCreateDefaultModulesWithPermissions();
        $accessControlService->truncateAndCreateDefaultRolesAndAssignPermissions();
        $accessControlService->truncateAndCreateDefaultUsersAndAssignRoles();

        $this->systemAdmin = User::find(ReservedRole::SYSTEM_ADMIN->value);
        $this->user = Role::where('name', 'user')->first()?->users()->first();
    }
}

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
        AccessControlService::truncateAndCreateDefaultModulesWithPermissions();
        AccessControlService::truncateAndCreateDefaultRolesAndAssignPermissions();
        AccessControlService::truncateAndCreateDefaultUsersAndAssignRoles();

        $this->systemAdmin = User::find(ReservedRole::SYSTEM_ADMIN->value);
        $this->user = Role::where('name', 'user')->first()?->users()->first();
    }
}

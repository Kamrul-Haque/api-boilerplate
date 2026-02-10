<?php

namespace App\Actions\Api\RoleActions;

use App\Actions\BaseAction;
use App\Models\Role;

class ShowRoleAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Role $role): Role
    {
        $role->authorizeOwnerOrPermission();

        return $role->load(['permissions', 'permissions.module']);
    }
}

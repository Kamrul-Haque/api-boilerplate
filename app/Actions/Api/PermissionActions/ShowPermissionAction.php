<?php

namespace App\Actions\Api\PermissionActions;

use App\Actions\BaseAction;
use App\Models\Permission;

class ShowPermissionAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Permission $permission): Permission
    {
        return $permission->load(['module']);
    }
}

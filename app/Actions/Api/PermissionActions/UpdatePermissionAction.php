<?php

namespace App\Actions\Api\PermissionActions;

use App\Actions\BaseAction;
use App\DTOs\Api\PermissionData;
use App\Models\Permission;

class UpdatePermissionAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(PermissionData $permissionData, Permission $permission): Permission
    {
        $permission->update($permissionData->toArray());

        return $permission->refresh()->load('module');
    }
}

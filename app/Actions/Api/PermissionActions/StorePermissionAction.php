<?php

namespace App\Actions\Api\PermissionActions;

use App\Actions\BaseAction;
use App\DTOs\Api\PermissionData;
use App\Models\Permission;

class StorePermissionAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(PermissionData $permissionData): Permission
    {
        $permission = Permission::create($permissionData->toArray());

        return $permission->refresh()->load('module');
    }
}

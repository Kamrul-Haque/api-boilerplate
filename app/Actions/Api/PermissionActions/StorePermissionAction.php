<?php

namespace App\Actions\Api\PermissionActions;

use App\Actions\BaseAction;
use App\Models\Permission;

class StorePermissionAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(mixed $validated): Permission
    {
        $permission = Permission::create($validated);

        return $permission->refresh()->load('module');
    }
}

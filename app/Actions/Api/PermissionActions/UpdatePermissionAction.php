<?php

namespace App\Actions\Api\PermissionActions;

use App\Actions\BaseAction;
use App\Models\Permission;

class UpdatePermissionAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(mixed $validated, Permission $permission): Permission
    {
        $permission->update($validated);

        return $permission->refresh()->load('module');
    }
}

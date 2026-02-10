<?php

namespace App\Actions\Api\PermissionActions;

use App\Actions\BaseAction;
use App\Models\Permission;

class DestroyPermissionAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Permission $permission): void
    {
        $permission->delete();
    }
}

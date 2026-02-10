<?php

namespace App\Actions\Api\RoleActions;

use App\Actions\BaseAction;
use App\Exceptions\ClientErrorException;
use App\Models\Role;
use Exception;

class DestroyRoleAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Exception
     */
    public function handle(Role $role): void
    {
        $role->authorizeOwnerOrPermission();

        if ($role->is_reserved) {
            throw new ClientErrorException(trans('common.cannot_delete_role'));
        }

        if (auth()->user()->active_role_id === $role->id) {
            throw new ClientErrorException(trans('common.cannot_delete_own_role'));
        }

        $role->delete();
    }
}

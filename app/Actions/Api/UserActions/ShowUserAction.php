<?php

namespace App\Actions\Api\UserActions;

use App\Actions\BaseAction;
use App\Models\User;

class ShowUserAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(User $user): User
    {
        $user->authorizeOwnerOrPermission();

        return $user->load(['active_role', 'active_role.permissions', 'roles']);
    }
}

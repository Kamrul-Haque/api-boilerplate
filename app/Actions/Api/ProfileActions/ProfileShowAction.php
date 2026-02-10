<?php

namespace App\Actions\Api\ProfileActions;

use App\Actions\BaseAction;
use App\Models\User;

class ProfileShowAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(User $user): User
    {
        return $user->load(['active_role', 'active_role.permissions', 'roles']);
    }
}

<?php

namespace App\Actions\Api\UserActions;

use App\Actions\BaseAction;
use App\Models\User;
use App\Services\TokenService;
use Exception;

class AssignRolesAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Exception
     */
    public function handle(array $roles, User $user): User
    {
        $user->roles()->detach();
        $user->update(['active_role_id' => null]);

        foreach ($roles as $role) {
            $user->assignRole((int) $role);
        }

        app(TokenService::class)->revokeAll($user);

        $user->load(['active_role', 'active_role.permissions', 'roles']);

        return $user->refresh();
    }
}

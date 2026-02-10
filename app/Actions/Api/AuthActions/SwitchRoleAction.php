<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SwitchRoleAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(int $role_id, User $user): User
    {
        if (! $user->roles->contains($role_id)) {
            throw ValidationException::withMessages(['role' => trans('common.role_not_found')]);
        }

        if ($user->active_role_id === $role_id) {
            throw ValidationException::withMessages(['role' => trans('common.role_already_active')]);
        }

        $user->update(['active_role_id' => $role_id]);

        $user->load(['active_role', 'active_role.permissions', 'roles']);

        return $user->refresh();
    }
}

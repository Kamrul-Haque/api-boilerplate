<?php

namespace App\Actions\Api\RoleActions;

use App\Actions\BaseAction;
use App\DTOs\Api\RoleData;
use App\Enums\ReservedRole;
use App\Exceptions\ClientErrorException;
use App\Models\Role;
use App\Services\AccessControlService;
use DB;
use Exception;
use Illuminate\Support\Arr;
use Throwable;

class UpdateRoleAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Exception
     * @throws Throwable
     */
    public function handle(RoleData $roleData, Role $role): Role
    {
        $role->authorizeOwnerOrPermission();

        if ($role->is_reserved && ! auth()->user()->hasRole(ReservedRole::SYSTEM_ADMIN->value)) {
            throw new ClientErrorException(trans('common.cannot_modify_role'));
        }

        if (auth()->user()->active_role_id === $role->id) {
            throw new ClientErrorException(trans('common.cannot_modify_own_role'));
        }

        return DB::transaction(function () use ($roleData, $role) {
            $validated = $roleData->toArray();
            $role->update(Arr::except($validated, 'permissions'));

            AccessControlService::assignPermissions($role, $validated['permissions']);

            return $role->refresh()->load(['permissions', 'permissions.module']);
        });
    }
}

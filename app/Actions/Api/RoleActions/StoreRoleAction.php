<?php

namespace App\Actions\Api\RoleActions;

use App\Actions\BaseAction;
use App\DTOs\Api\RoleData;
use App\Models\Role;
use App\Services\AccessControlService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreRoleAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Throwable
     */
    public function handle(RoleData $roleData): Role
    {
        return DB::transaction(function () use ($roleData) {
            $validated = $roleData->toArray();
            $role = Role::create(Arr::except($validated, 'permissions'));

            AccessControlService::assignPermissions($role, $validated['permissions']);

            return $role->refresh()->load(['permissions', 'permissions.module']);
        });
    }
}

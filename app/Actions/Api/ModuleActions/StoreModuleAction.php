<?php

namespace App\Actions\Api\ModuleActions;

use App\Actions\BaseAction;
use App\Models\Module;
use App\Services\AccessControlService;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreModuleAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Throwable
     */
    public function handle(mixed $validated): Module
    {
        return DB::transaction(function () use ($validated) {
            $module = Module::create($validated);

            AccessControlService::createPermissions($module);

            return $module->refresh()->load('permissions');
        });
    }
}

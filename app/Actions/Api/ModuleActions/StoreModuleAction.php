<?php

namespace App\Actions\Api\ModuleActions;

use App\Actions\BaseAction;
use App\DTOs\Api\ModuleData;
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
    public function handle(ModuleData $moduleData): Module
    {
        return DB::transaction(function () use ($moduleData) {
            $module = Module::create($moduleData->toArray());

            AccessControlService::createPermissions($module);

            return $module->refresh()->load('permissions');
        });
    }
}

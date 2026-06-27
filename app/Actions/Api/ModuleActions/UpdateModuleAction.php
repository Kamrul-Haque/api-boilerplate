<?php

namespace App\Actions\Api\ModuleActions;

use App\Actions\BaseAction;
use App\DTOs\Api\ModuleData;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateModuleAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Throwable
     */
    public function handle(ModuleData $moduleData, Module $module): Module
    {
        return DB::transaction(function () use ($moduleData, $module) {
            $module->update($moduleData->toArray());

            return $module->refresh()->load('permissions');
        });
    }
}

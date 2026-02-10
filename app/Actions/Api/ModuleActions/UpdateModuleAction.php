<?php

namespace App\Actions\Api\ModuleActions;

use App\Actions\BaseAction;
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
    public function handle(mixed $validated, Module $module): Module
    {
        return DB::transaction(function () use ($validated, $module) {
            $module->update($validated);

            return $module->refresh()->load('permissions');
        });
    }
}

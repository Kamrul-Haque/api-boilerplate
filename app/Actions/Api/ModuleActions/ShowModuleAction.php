<?php

namespace App\Actions\Api\ModuleActions;

use App\Actions\BaseAction;
use App\Models\Module;

class ShowModuleAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Module $module): Module
    {
        return $module->load(['permissions']);
    }
}

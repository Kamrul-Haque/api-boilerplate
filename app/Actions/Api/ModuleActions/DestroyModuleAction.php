<?php

namespace App\Actions\Api\ModuleActions;

use App\Actions\BaseAction;
use App\Models\Module;

class DestroyModuleAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Module $module): void
    {
        $module->delete();
    }
}

<?php

namespace App\Actions\Api\TrashActions;

use App\Actions\BaseAction;
use App\Models\Trash;

class ClearTrashAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(): void
    {
        Trash::truncate();
    }
}

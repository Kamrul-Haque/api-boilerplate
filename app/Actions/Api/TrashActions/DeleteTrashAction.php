<?php

namespace App\Actions\Api\TrashActions;

use App\Actions\BaseAction;
use App\Models\Trash;

class DeleteTrashAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Trash $trash): void
    {
        $trash->delete();
    }
}

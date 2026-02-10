<?php

namespace App\Actions\Api\TrashActions;

use App\Actions\BaseAction;
use App\Models\Trash;
use Exception;
use Illuminate\Support\Arr;

class RestoreTrashAction extends BaseAction
{
    /**
     * Restore a trash data by id.
     *
     * @throws Exception
     */
    public function handle(Trash $trash): void
    {
        $trash->trashable_type::create(Arr::except($trash->data, ['id', 'created_at', 'updated_at']));

        $trash->delete();
    }
}

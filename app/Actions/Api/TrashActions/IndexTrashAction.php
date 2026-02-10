<?php

namespace App\Actions\Api\TrashActions;

use App\Actions\BaseAction;
use App\Models\Trash;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelIdea\Helper\App\Models\_IH_Trash_C;

class IndexTrashAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(): _IH_Trash_C|LengthAwarePaginator|array
    {
        return Trash::paginate(30);
    }
}

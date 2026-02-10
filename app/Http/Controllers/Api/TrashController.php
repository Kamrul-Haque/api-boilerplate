<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\TrashActions\ClearTrashAction;
use App\Actions\Api\TrashActions\DeleteTrashAction;
use App\Actions\Api\TrashActions\IndexTrashAction;
use App\Actions\Api\TrashActions\RestoreTrashAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\TrashResource;
use App\Models\Trash;
use Dedoc\Scramble\Attributes\Group;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

#[Group(name: 'Trash Management')]
class TrashController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('can:view-trashes', only: ['index', 'show']),
            new Middleware('can:restore-trashes', only: ['restore']),
            new Middleware('can:delete-trashes', only: ['delete']),
            new Middleware('can:clear-trashes', only: ['clear']),
        ];
    }

    /**
     * Trash List
     *
     * Get a paginated list of trash data.
     *
     * @return AnonymousResourceCollection
     */
    public function index(IndexTrashAction $indexAction)
    {
        $trashes = $indexAction->handle();

        return TrashResource::collection($trashes);
    }

    /**
     * Restore Trash
     *
     * Restore trash data by id.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function restore(Trash $trash, RestoreTrashAction $restoreAction)
    {
        $restoreAction->handle($trash);

        return response()->json(['message' => 'Trash restored successfully.']);
    }

    /**
     * Delete Trash
     *
     * Delete trash data by id.
     *
     * @return JsonResponse
     */
    public function delete(Trash $trash, DeleteTrashAction $deleteAction)
    {
        $deleteAction->handle($trash);

        return response()->json(['message' => trans('success.deleted')]);
    }

    /**
     * Clear Trash
     *
     * Delete all trash at once.
     *
     * @return JsonResponse
     */
    public function clear(ClearTrashAction $clearAction)
    {
        $clearAction->handle();

        return response()->json(['message' => trans('success.trash_cleared')]);
    }
}

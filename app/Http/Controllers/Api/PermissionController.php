<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\PermissionActions\DestroyPermissionAction;
use App\Actions\Api\PermissionActions\IndexPermissionAction;
use App\Actions\Api\PermissionActions\ShowPermissionAction;
use App\Actions\Api\PermissionActions\StorePermissionAction;
use App\Actions\Api\PermissionActions\UpdatePermissionAction;
use App\DTOs\Api\PermissionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

#[Group(name: 'Permission Management')]
class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('can:view-permissions', only: ['index', 'show']),
            new Middleware('can:create-permissions', only: ['store']),
            new Middleware('can:update-permissions', only: ['update']),
            new Middleware('can:delete-permissions', only: ['destroy']),
        ];
    }

    /**
     * Permission List
     *
     * Get a paginated list of permissions.
     */
    #[QueryParameter('page', description: 'The current page number.', type: 'int', example: 2)]
    #[QueryParameter('search', description: 'Query string for searching the data by.', type: 'string', example: 'something')]
    #[QueryParameter('sortBy', description: 'Column name to sort data by.', type: 'string', default: 'id', example: 'name')]
    #[QueryParameter('sortDesc', description: 'Sort direction descending or ascending.', type: 'boolean', example: false)]
    #[QueryParameter('perPage', description: 'Number of items per page.', type: 'int', default: 30, example: 10)]
    public function index(Request $request, IndexPermissionAction $indexPermissionAction)
    {
        $data = $indexPermissionAction->handle($request);

        return PermissionResource::collection($data['permissions'])->additional(['filters' => $data['filters']]);
    }

    /**
     * Create Permission
     *
     * Create a new permission.
     */
    public function store(PermissionRequest $request, StorePermissionAction $storePermissionAction)
    {
        $permissionData = PermissionData::fromRequest($request);
        $permission = $storePermissionAction->handle($permissionData);

        return PermissionResource::make($permission);
    }

    /**
     * Get Permission
     *
     * Get a permission by permission_id.
     */
    public function show(Permission $permission, ShowPermissionAction $showPermissionAction)
    {
        $permission = $showPermissionAction->handle($permission);

        return PermissionResource::make($permission);
    }

    /**
     * Update Permission
     *
     * Update a permission by permission_id.
     */
    public function update(
        PermissionRequest $request,
        Permission $permission,
        UpdatePermissionAction $updatePermissionAction
    ) {
        $permissionData = PermissionData::fromRequest($request);
        $permission = $updatePermissionAction->handle($permissionData, $permission);

        return PermissionResource::make($permission);
    }

    /**
     * Delete Permission
     *
     * Delete a permission by permission_id.
     */
    public function destroy(Permission $permission, DestroyPermissionAction $destroyPermissionAction)
    {
        $destroyPermissionAction->handle($permission);

        return response()->json(['message' => trans('success.deleted')]);
    }
}

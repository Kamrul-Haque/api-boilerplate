<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\RoleActions\DestroyRoleAction;
use App\Actions\Api\RoleActions\IndexRoleAction;
use App\Actions\Api\RoleActions\ShowRoleAction;
use App\Actions\Api\RoleActions\StoreRoleAction;
use App\Actions\Api\RoleActions\UpdateRoleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RoleRequest;
use App\Http\Resources\RoleIndexResource;
use App\Http\Resources\RoleShowResource;
use App\Models\Role;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Throwable;

#[Group(name: 'Role Management')]
class RoleController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('can:view-roles', only: ['index', 'show']),
            new Middleware('can:create-roles', only: ['store']),
            new Middleware('can:update-roles', only: ['update']),
            new Middleware('can:delete-roles', only: ['destroy']),
        ];
    }

    /**
     * Role List
     *
     * Get a paginated list of roles.
     */
    #[QueryParameter('page', description: 'The current page number.', type: 'int', example: 2)]
    #[QueryParameter('search', description: 'Query string for searching the data by.', type: 'string', example: 'something')]
    #[QueryParameter('sortBy', description: 'Column name to sort data by.', type: 'string', default: 'id', example: 'name')]
    #[QueryParameter('sortDesc', description: 'Sort direction descending or ascending.', type: 'boolean', example: false)]
    #[QueryParameter('perPage', description: 'Number of items per page.', type: 'int', default: 30, example: 10)]
    #[QueryParameter('hide_reserved', description: 'Filter roles by hiding reserved ones.', type: 'string', format: 'boolean', example: true)]
    public function index(Request $request, IndexRoleAction $indexRoleAction)
    {
        $data = $indexRoleAction->handle($request);

        return RoleIndexResource::collection($data['roles'])->additional(['filters' => $data['filters']]);
    }

    /**
     * Create Role
     *
     * Create a new role.
     *
     * @throws Throwable
     */
    public function store(RoleRequest $request, StoreRoleAction $storeRoleAction)
    {
        $role = $storeRoleAction->handle($request->validated());

        return RoleShowResource::make($role);
    }

    /**
     * Get Role
     *
     * Get a role by role_id.
     */
    public function show(Role $role, ShowRoleAction $showRoleAction)
    {
        $role = $showRoleAction->handle($role);

        return RoleShowResource::make($role);
    }

    /**
     * Update Role
     *
     * Update a role by role_id.
     *
     * @throws Exception|Throwable
     */
    public function update(RoleRequest $request, Role $role, UpdateRoleAction $updateRoleAction)
    {
        $role = $updateRoleAction->handle($request->validated(), $role);

        return RoleShowResource::make($role);
    }

    /**
     * Delete Role
     *
     * Delete a role by role_id.
     *
     * @throws Exception
     */
    public function destroy(Role $role, DestroyRoleAction $destroyRoleAction)
    {
        $destroyRoleAction->handle($role);

        return response()->json(['message' => trans('success.deleted')]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\UserActions\AssignRolesAction;
use App\Actions\Api\UserActions\DestroyUserAction;
use App\Actions\Api\UserActions\ImportUserAction;
use App\Actions\Api\UserActions\IndexUserAction;
use App\Actions\Api\UserActions\ShowUserAction;
use App\Actions\Api\UserActions\StoreUserAction;
use App\Actions\Api\UserActions\UpdateUserAction;
use App\DTOs\Api\UserData;
use App\Exceptions\ClientErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserImportRequest;
use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Throwable;

#[Group(name: 'User Management')]
class UserController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('can:view-users', only: ['index', 'show']),
            new Middleware('can:create-users', only: ['store']),
            new Middleware('can:update-users', only: ['update']),
            new Middleware('can:delete-users', only: ['destroy']),
            new Middleware('can:assign-roles-users', only: ['assignRoles']),
        ];
    }

    /**
     * User List
     *
     * Get a paginated list of users.
     *
     * @return AnonymousResourceCollection
     *
     * @throws ClientErrorException
     */
    #[QueryParameter('page', description: 'The current page number.', type: 'int', example: 2)]
    #[QueryParameter('search', description: 'Query string for searching the data by.', type: 'string', example: 'something')]
    #[QueryParameter('sortBy', description: 'Column name to sort data by.', type: 'string', default: 'id', example: 'name')]
    #[QueryParameter('sortDesc', description: 'Sort direction descending or ascending.', type: 'boolean', example: false)]
    #[QueryParameter('perPage', description: 'Number of items per page.', type: 'int', default: 30, example: 10)]
    #[QueryParameter('only_role', description: 'Get user list only for given role id.', type: 'string', format: 'int', example: 5)]
    #[QueryParameter('except_role', description: 'Get user list except for given role id.', type: 'string', format: 'int', example: 5)]
    public function index(Request $request, IndexUserAction $indexUserAction)
    {
        $data = $indexUserAction->handle($request);

        return UserResource::collection($data['users'])->additional(['filters' => $data['filters']]);
    }

    /**
     * Create User
     *
     * Create a new user.
     *
     * @return UserResource
     *
     * @throws Exception
     * @throws Throwable
     */
    public function store(UserRequest $request, StoreUserAction $storeUserAction)
    {
        $userData = UserData::fromRequest($request);
        $user = $storeUserAction->handle($userData);

        return UserResource::make($user);
    }

    /**
     * Get User
     *
     * Get a user by user_id.
     *
     * @group Users
     *
     * @return UserResource
     */
    public function show(User $user, ShowUserAction $showUserAction)
    {
        $user = $showUserAction->handle($user);

        return UserResource::make($user);
    }

    /**
     * Update User
     *
     * Update a user by user_id.
     *
     * @return UserResource
     *
     * @throws Exception
     * @throws Throwable
     */
    public function update(UserRequest $request, User $user, UpdateUserAction $updateUserAction)
    {
        $userData = UserData::fromRequest($request);
        $user = $updateUserAction->handle($userData, $user);

        return UserResource::make($user);
    }

    /**
     * Delete User
     *
     * Delete a user by user_id.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(User $user, DestroyUserAction $destroyUserAction)
    {
        $destroyUserAction->handle($user, auth()->user()->id);

        return response()->json(['message' => trans('success.deleted')]);
    }

    /**
     * Assign Role
     *
     * Assign roles to a user by user_id
     *
     * @return JsonResponse
     */
    public function assignRoles(Request $request, User $user, AssignRolesAction $assignRolesAction)
    {
        $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'integer', 'exists:roles,id'],
        ]);

        try {
            $user = $assignRolesAction->handle($request->input('roles'), $user);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return response()->json([
            'message' => 'Roles assigned successfully.',
            'user' => UserResource::make($user),
        ]);
    }

    /**
     * User Import
     *
     * Import users' data from csv file
     *
     * @return JsonResponse
     *
     * @throws ClientErrorException
     */
    public function importCsv(UserImportRequest $request, ImportUserAction $importUserAction)
    {
        $importUserAction->handle($request->file('file'));

        return response()->json(['message' => trans('success.file_imported')]);
    }
}

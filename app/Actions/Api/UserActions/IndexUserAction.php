<?php

namespace App\Actions\Api\UserActions;

use App\Actions\BaseAction;
use App\Exceptions\ClientErrorException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexUserAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws ClientErrorException
     */
    public function handle(Request $request): array
    {
        $search = $request->search;
        $roleId = $request->only_role;
        $notRoleId = $request->except_role;

        if (! is_null($roleId) && ! (is_int($roleId) || DB::table('roles')->where('id', $roleId)->exists())) {
            throw new ClientErrorException(trans('common.invalid_role'));
        }

        if (! is_null($notRoleId) && ! (is_int($notRoleId) || DB::table('roles')->where('id', $notRoleId)->exists())) {
            throw new ClientErrorException(trans('common.invalid_role'));
        }

        $userId = auth()->user()?->hasPermission('view-all-users') ? null : auth()->user()?->id;

        return [
            'users' => User::query()
                ->with(['roles'])
                ->where(function ($query) use ($userId) {
                    $query->when($userId, function ($query) use ($userId) {
                        $query->where('created_by_id', $userId);
                    });
                })
                ->where(function ($query) use ($search) {
                    $query->when($search, function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%")
                            ->orWhereHas('roles', function ($query) use ($search) {
                                $query->where('name', 'LIKE', "%{$search}%");
                            });
                    });
                })
                ->where(function ($query) use ($roleId) {
                    $query->when($roleId, function ($query) use ($roleId) {
                        $query->whereHas('roles', function ($query) use ($roleId) {
                            $query->where('id', $roleId);
                        });
                    });
                })
                ->where(function ($query) use ($notRoleId) {
                    $query->when($notRoleId, function ($query) use ($notRoleId) {
                        $query->whereHas('roles', function ($query) use ($notRoleId) {
                            $query->whereNot('id', $notRoleId);
                        });
                    });
                })
                ->when($request->sortBy, function ($query, $sortBy) {
                    $query->orderBy($sortBy, request()->boolean('sortDesc') ? 'desc' : 'asc');
                }, function ($query) {
                    $query->orderBy('id', 'desc');
                })
                ->paginate($request->perPage ?? 30)
                ->withQueryString(),
            'filters' => [
                'role' => $request->role,
                'search' => $request->search,
                'sortBy' => $request->sortBy ?? 'id',
                'sortDesc' => $request->sortDesc ?? false,
                'perPage' => $request->perPage ?? 30,
            ],
        ];
    }
}

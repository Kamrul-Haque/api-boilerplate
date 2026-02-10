<?php

namespace App\Actions\Api\RoleActions;

use App\Actions\BaseAction;
use App\Enums\ReservedRole;
use App\Models\Role;
use Illuminate\Http\Request;

class IndexRoleAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Request $request): array
    {
        $search = $request->search;
        $hideReserved = $request->boolean('hide_reserved');
        $userId = auth()->user()?->hasPermission('view-all-roles') ? null : auth()->user()?->id;

        return [
            'roles' => Role::query()
                ->where(function ($query) use ($userId) {
                    $query->when($userId, function ($query) use ($userId) {
                        $query->where('created_by_id', $userId);
                    });
                })
                ->where(function ($query) use ($hideReserved) {
                    $query->when($hideReserved, function ($query) {
                        $query->whereNotIn('id', [
                            ReservedRole::SYSTEM_ADMIN->value,
                            ReservedRole::PARENT->value,
                        ]);
                    });
                })
                ->where(function ($query) use ($search) {
                    $query->when($search, function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('display_name', 'LIKE', "%{$search}%");
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
                'search' => $request->search,
                'sortBy' => $request->sortBy ?? 'id',
                'sortDesc' => $request->sortDesc ?? false,
                'perPage' => $request->perPage ?? 30,
            ],
        ];
    }
}

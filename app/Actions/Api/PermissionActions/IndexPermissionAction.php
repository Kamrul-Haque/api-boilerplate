<?php

namespace App\Actions\Api\PermissionActions;

use App\Actions\BaseAction;
use App\Models\Permission;
use Illuminate\Http\Request;

class IndexPermissionAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Request $request): array
    {
        $search = $request->search;

        return [
            'permissions' => Permission::query()
                ->with(['module'])
                ->where(function ($query) use ($search) {
                    $query->when($search, function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('display_name', 'LIKE', "%{$search}%")
                            ->orWhereHas('module', function ($query) use ($search) {
                                $query->where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('display_name', 'LIKE', "%{$search}%");
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
                'search' => $request->search,
                'sortBy' => $request->sortBy ?? 'id',
                'sortDesc' => $request->sortDesc ?? false,
                'perPage' => $request->perPage ?? 30,
            ],
        ];
    }
}

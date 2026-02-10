<?php

namespace App\Actions\Api\ModuleActions;

use App\Actions\BaseAction;
use App\Models\Module;
use Illuminate\Http\Request;

class IndexModuleAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(Request $request): array
    {
        $search = $request->search;

        return [
            'modules' => Module::query()
                ->with(['permissions'])->where('name', '!=', 'Trash')
                ->where(function ($query) use ($search) {
                    $query->when($search, function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('route_prefix', 'LIKE', "%{$search}%");
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

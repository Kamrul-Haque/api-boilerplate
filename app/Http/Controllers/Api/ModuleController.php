<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\ModuleActions\DestroyModuleAction;
use App\Actions\Api\ModuleActions\IndexModuleAction;
use App\Actions\Api\ModuleActions\ShowModuleAction;
use App\Actions\Api\ModuleActions\StoreModuleAction;
use App\Actions\Api\ModuleActions\UpdateModuleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ModuleRequest;
use App\Http\Resources\ModuleResource;
use App\Models\Module;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Throwable;

#[Group(name: 'Module Management')]
class ModuleController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('can:view-modules', only: ['index', 'show']),
            new Middleware('can:create-modules', only: ['store']),
            new Middleware('can:update-modules', only: ['update']),
            new Middleware('can:delete-modules', only: ['destroy']),
        ];
    }

    /**
     * Module List
     *
     * Get a paginated list of modules.
     */
    #[QueryParameter('page', description: 'The current page number.', type: 'int', example: 2)]
    #[QueryParameter('search', description: 'Query string for searching the data by.', type: 'string', example: 'something')]
    #[QueryParameter('sortBy', description: 'Column name to sort data by.', type: 'string', default: 'id', example: 'name')]
    #[QueryParameter('sortDesc', description: 'Sort direction descending or ascending.', type: 'boolean', example: false)]
    #[QueryParameter('perPage', description: 'Number of items per page.', type: 'int', default: 30, example: 10)]
    public function index(Request $request, IndexModuleAction $indexModuleAction)
    {
        $data = $indexModuleAction->handle($request);

        return ModuleResource::collection($data['modules'])->additional(['filters' => $data['filters']]);
    }

    /**
     * Create Module
     *
     * Create a new module.
     *
     * @throws Throwable
     */
    public function store(ModuleRequest $request, StoreModuleAction $storeModuleAction)
    {
        $module = $storeModuleAction->handle($request->validated());

        return ModuleResource::make($module);
    }

    /**
     * Get Module
     *
     * Get a module by module_id.
     */
    public function show(Module $module, ShowModuleAction $showModuleAction)
    {
        $module = $showModuleAction->handle($module);

        return ModuleResource::make($module);
    }

    /**
     * Update Module
     *
     * Update a module by module_id.
     *
     * @throws Throwable
     */
    public function update(ModuleRequest $request, Module $module, UpdateModuleAction $updateModuleAction)
    {
        $module = $updateModuleAction->handle($request->validated(), $module);

        return ModuleResource::make($module);
    }

    /**
     * Delete Module
     *
     * Delete a module by module_id.
     */
    public function destroy(Module $module, DestroyModuleAction $destroyModuleAction)
    {
        $destroyModuleAction->handle($module);

        return response()->json(['message' => trans('success.deleted')]);
    }
}

<?php

namespace App\Services;

use App\Enums\ReservedRole;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccessControlService
{
    /**
     * Modules of the application
     */
    private static array $modules = [
        [
            'name' => 'Users', 'route_prefix' => 'users',
            'description' => 'Manage access for user management functionalities.',
        ],
        [
            'name' => 'Roles', 'route_prefix' => 'roles',
            'description' => 'Manage access for role management functionalities.',
        ],
        [
            'name' => 'Modules', 'route_prefix' => 'modules',
            'description' => 'Manage access for module management functionalities.',
        ],
        [
            'name' => 'Permissions', 'route_prefix' => 'permissions',
            'description' => 'Manage access for permission management functionalities.',
        ],
    ];

    /**
     * Basic permissions required for CRUD operation
     */
    private static array $basePermissions = [
        ['name' => 'view', 'display_name' => 'View', 'is_assignable' => true],
        ['name' => 'view-all', 'display_name' => 'View All', 'is_assignable' => true],
        ['name' => 'create', 'display_name' => 'Create', 'is_assignable' => true],
        ['name' => 'update', 'display_name' => 'Update', 'is_assignable' => true],
        ['name' => 'delete', 'display_name' => 'Delete', 'is_assignable' => true],
    ];

    /**
     * Modules that different permissions than base permissions required for CRUD operation
     */
    private static array $specialModules = [
        [
            'name' => 'Trash', 'route_prefix' => 'trashes',
            'description' => 'Manage access for trashed data management functionalities.',
        ],
    ];

    /**
     * Permissions by module route_prefix required for additional functionalities other than CRUD operation
     */
    private static array $additionalPermissions = [
        'users' => [
            ['name' => 'assign-roles', 'display_name' => 'Assign Roles', 'is_assignable' => true],
        ],
        'trashes' => [
            ['name' => 'view', 'display_name' => 'View', 'is_assignable' => true],
            ['name' => 'restore', 'display_name' => 'Restore', 'is_assignable' => true],
            ['name' => 'delete', 'display_name' => 'Delete', 'is_assignable' => true],
            ['name' => 'clear', 'display_name' => 'Clear', 'is_assignable' => true],
        ],
    ];

    /**
     * Default roles of the application
     */
    private static array $roles = [
        [
            'display_name' => 'System Admin',
            'name' => 'system-admin',
            'is_reserved' => true,
            'description' => 'Has full system-level access including server and configuration management.',
        ],
        [
            'display_name' => 'Super Admin',
            'name' => 'super-admin',
            'is_reserved' => false,
            'description' => 'Manages all modules and user roles within the application.',
        ],
        [
            'display_name' => 'Admin',
            'name' => 'admin',
            'is_reserved' => false,
            'description' => 'Oversees day-to-day administrative tasks such as user management and reports.',
        ],
        [
            'display_name' => 'User',
            'name' => 'user',
            'is_reserved' => false,
            'description' => 'End users of the application.',
        ],
    ];

    /**
     * Default users of the system
     */
    private static array $users = [
        ['name' => 'System Admin', 'email' => 'system@admin.com'],
        ['name' => 'Super Admin', 'email' => 'super@admin.com'],
        ['name' => 'Mr. Admin', 'email' => 'admin@email.com'],
        ['name' => 'Mr. User', 'email' => 'user@email.com'],
    ];

    /**
     * Get a list of modules for seeding access control
     */
    public static function getModules(): array
    {
        return self::$modules;
    }

    /**
     * Get base permissions to auto-generate for each module
     */
    public static function getBasePermissions(): array
    {
        return self::$basePermissions;
    }

    /**
     * Truncate the role & permissions table and create default roles with permissions
     */
    public static function truncateAndCreateDefaultModulesWithPermissions(): void
    {
        if (Schema::hasTable('modules') && DB::table('modules')->count()) {
            DB::table('modules')->delete();
            DB::statement('ALTER TABLE modules AUTO_INCREMENT = 1;');
        }

        if (Schema::hasTable('permissions') && DB::table('permissions')->count()) {
            DB::table('permissions')->delete();
            DB::statement('ALTER TABLE permissions AUTO_INCREMENT = 1;');
        }

        $modules = self::$modules;
        $specialModules = self::$specialModules;

        foreach ($modules as $module) {
            $module = Module::create($module);

            self::createPermissions($module);

            self::createAdditionalPermissions($module);
        }

        foreach ($specialModules as $module) {
            $module = Module::create($module);

            self::createAdditionalPermissions($module);
        }
    }

    /**
     * Create corresponding permissions for a given module
     */
    public static function createPermissions(Module $module, ?string $routePrefix = null): void
    {
        $basePermissions = self::$basePermissions;
        $routePrefix = $routePrefix ?? $module->route_prefix;

        $systemAdminRole = Schema::hasTable('roles') ? Role::where('name', 'system-admin')->first() : null;

        foreach ($basePermissions as $basePermission) {
            $permission = null;
            $name = $basePermission['name'].'-'.$routePrefix;
            $existingPermission = Permission::where('name', $name)->exists();

            if (! $existingPermission) {
                $permission = $module->permissions()->create([
                    'name' => $name,
                    'display_name' => $basePermission['display_name'],
                    'is_assignable' => $basePermission['is_assignable'],
                ]);
            }

            if ($permission && $systemAdminRole) {
                $systemAdminRole->permissions()->syncWithoutDetaching($permission);
            }
        }
    }

    /**
     * Creates additional for the required modules
     */
    public static function createAdditionalPermissions(Module $module): void
    {
        $permissions = self::getAdditionalPermissionsByModule($module->route_prefix);

        if (count($permissions)) {
            foreach ($permissions as $permission) {
                $name = $permission['name'].'-'.$module->route_prefix;

                $module->permissions()->create([
                    'name' => $name,
                    'display_name' => $permission['display_name'],
                    'is_assignable' => $permission['is_assignable'],
                ]);
            }
        }
    }

    /**
     * Get additional permissions by module
     */
    public static function getAdditionalPermissionsByModule(string $module): array
    {
        $additionalPermissions = collect(self::$additionalPermissions);

        return (array) $additionalPermissions->get($module, []);
    }

    /**
     * Truncate roles table and create default roles
     */
    public static function truncateAndCreateDefaultRolesAndAssignPermissions(): void
    {
        if (Schema::hasTable('roles') && DB::table('roles')->count()) {
            DB::table('roles')->delete();
            DB::statement('ALTER TABLE roles AUTO_INCREMENT = 1;');
        }

        $roles = self::$roles;

        foreach ($roles as $role) {
            $role = Role::create($role);

            $permissions = self::getRoleWiseAssignablePermissions($role->name);

            $role->permissions()->syncWithoutDetaching($permissions);
        }
    }

    /**
     * Get all assignable permissions by the given role name
     */
    public static function getRoleWiseAssignablePermissions(string $roleName): Collection
    {
        $permissions = collect();

        switch ($roleName) {
            case 'system-admin':
                $permissions = Permission::query()
                    ->pluck('id');
                break;
            case 'super-admin':
                $permissions = Permission::query()
                    ->where('name', 'NOT LIKE', '%modules')
                    ->where('name', 'NOT LIKE', '%permissions')
                    ->where('name', 'NOT LIKE', '%trashes')
                    ->pluck('id');
                $viewPermissions = Permission::query()
                    ->where('name', 'LIKE', 'view%')
                    ->pluck('id');
                $permissions = $permissions->merge($viewPermissions)->unique()->values();
                break;
            case 'admin':
                $permissions = Permission::query()
                    ->where('name', 'NOT LIKE', '%modules')
                    ->where('name', 'NOT LIKE', '%permissions')
                    ->where('name', 'NOT LIKE', '%trashes')
                    ->where('name', 'NOT LIKE', 'delete-roles')
                    ->where('name', 'NOT LIKE', 'delete-users')
                    ->pluck('id');
                $viewPermissions = Permission::query()
                    ->where('name', 'LIKE', 'view%')
                    ->pluck('id');
                $permissions = $permissions->merge($viewPermissions)->unique()->values();
                break;
            case 'user':
                $permissions = Permission::query()
                    ->where('name', 'NOT LIKE', '%modules')
                    ->where('name', 'NOT LIKE', '%permissions')
                    ->where('name', 'NOT LIKE', '%trashes')
                    ->where('name', 'NOT LIKE', '%roles')
                    ->where('name', 'NOT LIKE', '%users')
                    ->pluck('id');
                break;
        }

        return $permissions;
    }

    /**
     * Truncate users table and create default users to test the system
     *
     * @throws Exception
     */
    public static function truncateAndCreateDefaultUsersAndAssignRoles(): void
    {
        if (! app()->isProduction()) {
            if (Schema::hasTable('users') && DB::table('users')->count()) {
                DB::table('users')->delete();
                DB::statement('ALTER TABLE users AUTO_INCREMENT = 1;');
            }
        }

        $password = bcrypt('Pa$$word');

        $users = self::$users;

        foreach ($users as $index => $user) {
            $user['password'] = $password;

            $user = User::create($user);

            $user->assignRole(self::$roles[$index]['name']);

            if (app()->isProduction()) {
                break;
            }
        }
    }

    /**
     * Assign permissions to a given role
     */
    public static function assignPermissions(Role $role, array $permissions): void
    {
        $role->permissions()->detach();

        foreach ($permissions as $key => $permission) {
            $permission = Permission::with('module')->find($permission);

            $view_permission = Permission::where('module_id', $permission->module_id)
                ->where('name', 'view-'.$permission->module->route_prefix)
                ->first();

            if ($view_permission) {
                $role->permissions()->syncWithoutDetaching($view_permission);
            }

            $role->permissions()->syncWithoutDetaching($permissions[$key]);
        }
    }

    /**
     * Authorize that the current user is either the creator of the model
     * or has the given permission.
     *
     * @param  object  $model  Model instance with created_by_id property
     * @param  string|null  $permission  Optional permission name to check
     * @param  callable|null  $additionalCheck  Optional callable for additional ownership checks (receives $model, returns bool)
     *
     * @throws AccessDeniedHttpException
     */
    public static function authorizeOwnerOrPermission(
        object $model,
        ?string $permission = null,
        ?callable $additionalCheck = null
    ): void {
        $user = auth()->user();

        if ($user->active_role_id === ReservedRole::SYSTEM_ADMIN->value) {
            return;
        }

        if ($model->created_by_id === $user->id) {
            return;
        }

        if ($additionalCheck && $additionalCheck($model)) {
            return;
        }

        $viewAllPermission = 'view-all-'.Str::plural(Str::kebab(class_basename($model)));

        if ($user->hasPermission($permission ?? $viewAllPermission)) {
            return;
        }

        throw new AccessDeniedHttpException(trans('common.unauthorized_access_permission'));
    }
}

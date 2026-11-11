<?php

namespace App\Services;

use App\Enums\ReservedRole;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccessControlService
{
    /**
     * Modules of the application
     */
    protected array $modules = [
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
    protected array $basePermissions = [
        ['name' => 'view', 'display_name' => 'View', 'is_assignable' => true],
        ['name' => 'view-all', 'display_name' => 'View All', 'is_assignable' => true],
        ['name' => 'create', 'display_name' => 'Create', 'is_assignable' => true],
        ['name' => 'update', 'display_name' => 'Update', 'is_assignable' => true],
        ['name' => 'delete', 'display_name' => 'Delete', 'is_assignable' => true],
    ];

    /**
     * Modules that different permissions than base permissions required for CRUD operation
     */
    protected array $specialModules = [
        [
            'name' => 'Trash', 'route_prefix' => 'trashes',
            'description' => 'Manage access for trashed data management functionalities.',
        ],
    ];

    /**
     * Permissions by module route_prefix required for additional functionalities other than CRUD operation
     */
    protected array $additionalPermissions = [
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
    protected array $roles = [
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
    protected array $users = [
        ['name' => 'System Admin', 'email' => 'system@admin.com'],
        ['name' => 'Super Admin', 'email' => 'super@admin.com'],
        ['name' => 'Mr. Admin', 'email' => 'admin@email.com'],
        ['name' => 'Mr. User', 'email' => 'user@email.com'],
    ];

    /**
     * Create a new AccessControlService instance.
     */
    public function __construct(
        protected DatabaseManager $db,
        protected AuthFactory $auth,
        protected Hasher $hasher,
        protected Application $app,
        protected Translator $translator,
    ) {}

    /**
     * Get the database schema builder instance.
     */
    protected function schema(): SchemaBuilder
    {
        return $this->db->getSchemaBuilder();
    }

    /**
     * Reset the auto-increment counter of the given table for the current driver.
     */
    protected function resetAutoIncrement(string $table): void
    {
        switch ($this->db->connection()->getDriverName()) {
            case 'mysql':
            case 'mariadb':
                $this->db->statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                break;
            case 'pgsql':
                $this->db->statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH 1");
                break;
            case 'sqlite':
                if ($this->schema()->hasTable('sqlite_sequence')) {
                    $this->db->table('sqlite_sequence')->where('name', $table)->delete();
                }
                break;
            case 'sqlsrv':
                $this->db->statement("DBCC CHECKIDENT ('{$table}', RESEED, 0)");
                break;
        }
    }

    /**
     * Get a list of modules for seeding access control
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Get base permissions to auto-generate for each module
     */
    public function getBasePermissions(): array
    {
        return $this->basePermissions;
    }

    /**
     * Truncate the role & permissions table and create default modules with permissions
     */
    public function truncateAndCreateDefaultModulesWithPermissions(): void
    {
        if ($this->schema()->hasTable('modules')) {
            $this->db->table('modules')->delete();
            $this->resetAutoIncrement('modules');
        }

        if ($this->schema()->hasTable('permissions')) {
            $this->db->table('permissions')->delete();
            $this->resetAutoIncrement('permissions');
        }

        $modules = $this->modules;
        $specialModules = $this->specialModules;

        foreach ($modules as $module) {
            $module = Module::create($module);

            $this->createPermissions($module);

            $this->createAdditionalPermissions($module);
        }

        foreach ($specialModules as $module) {
            $module = Module::create($module);

            $this->createAdditionalPermissions($module);
        }
    }

    /**
     * Create corresponding permissions for a given module
     */
    public function createPermissions(Module $module, ?string $routePrefix = null): void
    {
        $basePermissions = $this->basePermissions;
        $routePrefix = $routePrefix ?? $module->route_prefix;

        $systemAdminRole = $this->schema()->hasTable('roles') ? Role::where('name', 'system-admin')->first() : null;

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
    public function createAdditionalPermissions(Module $module): void
    {
        $permissions = $this->getAdditionalPermissionsByModule($module->route_prefix);

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
    public function getAdditionalPermissionsByModule(string $module): array
    {
        $additionalPermissions = collect($this->additionalPermissions);

        return (array) $additionalPermissions->get($module, []);
    }

    /**
     * Truncate roles table and create default roles
     */
    public function truncateAndCreateDefaultRolesAndAssignPermissions(): void
    {
        if ($this->schema()->hasTable('roles')) {
            $this->db->table('roles')->delete();
            $this->resetAutoIncrement('roles');
        }

        $roles = $this->roles;

        foreach ($roles as $role) {
            $role = Role::create($role);

            $permissions = $this->getRoleWiseAssignablePermissions($role->name);

            $role->permissions()->syncWithoutDetaching($permissions);
        }
    }

    /**
     * Get all assignable permissions by the given role name
     */
    public function getRoleWiseAssignablePermissions(string $roleName): Collection
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
    public function truncateAndCreateDefaultUsersAndAssignRoles(): void
    {
        if (! $this->app->environment('production')) {
            if ($this->schema()->hasTable('users')) {
                $this->db->table('users')->delete();
                $this->resetAutoIncrement('users');
            }
        }

        $password = $this->hasher->make('Pa$$word');

        $users = $this->users;

        foreach ($users as $index => $user) {
            $user['password'] = $password;

            $user = User::create($user);

            $user->assignRole($this->roles[$index]['name']);

            if ($this->app->environment('production')) {
                break;
            }
        }
    }

    /**
     * Assign permissions to a given role
     */
    public function assignPermissions(Role $role, array $permissions): void
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
    public function authorizeOwnerOrPermission(
        object $model,
        ?string $permission = null,
        ?callable $additionalCheck = null
    ): void {
        $user = $this->auth->guard()->user();

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

        throw new AccessDeniedHttpException($this->translator->get('common.unauthorized_access_permission'));
    }
}

<?php

namespace Database\Seeders;

use App\Services\AccessControlService;
use Exception;
use Illuminate\Database\Seeder;

class AccessControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws Exception
     */
    public function run(): void
    {
        AccessControlService::truncateAndCreateDefaultModulesWithPermissions();
        AccessControlService::truncateAndCreateDefaultRolesAndAssignPermissions();
        AccessControlService::truncateAndCreateDefaultUsersAndAssignRoles();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * This seeder runs in the tenant database context when a new tenant is created.
 */
class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Central (platform-level) seeders
        $this->call([
            PlansSeeder::class,
            PlatformAdminSeeder::class,
        ]);
    }
}

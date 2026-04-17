<?php

namespace Database\Seeders;

use App\Models\PlatformAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        PlatformAdmin::firstOrCreate(
            ['email' => env('PLATFORM_ADMIN_EMAIL', 'admin@saas.test')],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make(env('PLATFORM_ADMIN_PASSWORD', 'changeme123!')),
            ]
        );
    }
}

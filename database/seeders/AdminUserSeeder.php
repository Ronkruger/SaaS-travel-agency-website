<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::firstOrCreate(
            ['email' => 'admin@discovergrp.com'],
            [
                'name'         => 'DiscoverGRP Admin',
                'password'     => Hash::make(env('ADMIN_PASSWORD', 'Admin@1234!')),
                'department'   => 'executives',
                'position'     => 'Operations Manager',
                'is_onboarded' => true,
            ]
        );
    }
}

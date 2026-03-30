<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_users')->insertOrIgnore([
            'name'         => 'DiscoverGRP Admin',
            'email'        => 'admin@discovergrp.com',
            'password'     => Hash::make(env('ADMIN_PASSWORD', 'Admin@1234!')),
            'department'   => 'it',
            'position'     => 'Web Developer',
            'is_onboarded' => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('admin_users')->where('email', 'admin@discovergrp.com')->delete();
    }
};

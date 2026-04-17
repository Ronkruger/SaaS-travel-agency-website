<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_users')
            ->where('email', 'admin@discovergrp.com')
            ->update([
                'department'  => 'it',
                'position'    => 'Web Developer',
                'updated_at'  => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('admin_users')
            ->where('email', 'admin@discovergrp.com')
            ->update([
                'department'  => 'executives',
                'position'    => 'Operations Manager',
                'updated_at'  => now(),
            ]);
    }
};

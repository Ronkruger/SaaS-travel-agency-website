<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: each tenant's super admin is created via the registration flow.
        // Previously seeded a hardcoded developer account — removed for SaaS multi-tenancy.
    }

    public function down(): void
    {
        DB::table('admin_users')->where('email', 'admin@discovergrp.com')->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->string('role', 30)->default('staff')->after('position');
        });

        // Set existing IT Web Developer as super_admin
        DB::table('admin_users')
            ->where('department', 'it')
            ->where('position', 'Web Developer')
            ->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

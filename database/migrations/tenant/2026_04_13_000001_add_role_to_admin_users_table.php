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

        // Promote the tenant owner (executives / Owner) to super_admin.
        // Falls back to promoting any existing IT/Web Developer to preserve legacy behaviour.
        $promoted = DB::table('admin_users')
            ->where('department', 'executives')
            ->where('position', 'Owner')
            ->update(['role' => 'super_admin']);

        if ($promoted === 0) {
            DB::table('admin_users')
                ->where('department', 'it')
                ->where('position', 'Web Developer')
                ->update(['role' => 'super_admin']);
        }
    }

    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

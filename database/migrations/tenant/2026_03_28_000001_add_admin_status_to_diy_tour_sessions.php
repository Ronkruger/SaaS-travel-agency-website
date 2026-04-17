<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diy_tour_sessions', function (Blueprint $table) {
            $table->enum('admin_status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('diy_tour_sessions', function (Blueprint $table) {
            $table->dropColumn('admin_status');
        });
    }
};

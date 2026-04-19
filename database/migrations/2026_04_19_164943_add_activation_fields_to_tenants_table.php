<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('activation_token')->nullable()->after('password');
            $table->timestamp('activated_at')->nullable()->after('activation_token');
            $table->boolean('trial_activated')->default(false)->after('activated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['activation_token', 'activated_at', 'trial_activated']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->string('key', 100)->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });

            // Seed default values
            DB::table('settings')->insert([
            ['key' => 'company_name',        'value' => 'Discover Group',              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_tagline',      'value' => 'Creating Unforgettable Travel Experiences Since 2008', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logo_path',            'value' => null,                          'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logo_dark_path',       'value' => null,                          'created_at' => now(), 'updated_at' => now()],
                ['key' => 'favicon_path',         'value' => null,                          'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

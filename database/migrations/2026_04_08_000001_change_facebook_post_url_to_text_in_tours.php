<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ->change() requires doctrine/dbal on MySQL; SQLite has no strict column types.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        Schema::table('tours', function (Blueprint $table) {
            $table->text('facebook_post_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        Schema::table('tours', function (Blueprint $table) {
            $table->string('facebook_post_url')->nullable()->change();
        });
    }
};

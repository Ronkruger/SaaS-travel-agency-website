<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ->change() requires doctrine/dbal; SQLite already allows NULL on these columns.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('contact_email')->nullable()->change();
            $table->string('contact_phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->string('contact_email')->nullable(false)->change();
            $table->string('contact_phone')->nullable(false)->change();
        });
    }
};

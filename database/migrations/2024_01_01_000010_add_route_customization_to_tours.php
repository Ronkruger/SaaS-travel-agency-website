<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            // Allows admins to set a custom URL slug
            // (slug column already exists, no change needed there)

            // Geographical route stops: departure → waypoints → arrival
            $table->json('route_stops')->nullable()->after('itinerary');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn('route_stops');
        });
    }
};

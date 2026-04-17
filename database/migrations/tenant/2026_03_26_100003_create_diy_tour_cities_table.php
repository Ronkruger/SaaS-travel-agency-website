<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diy_tour_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('diy_tour_itineraries')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->string('city_name', 100);
            $table->string('country', 100);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedSmallInteger('day_start');
            $table->unsignedSmallInteger('day_end');
            $table->unsignedSmallInteger('duration_days');
            $table->enum('hotel_tier', ['3-star', '4-star', '5-star'])->default('4-star');
            $table->decimal('estimated_cost_php', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['itinerary_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diy_tour_cities');
    }
};

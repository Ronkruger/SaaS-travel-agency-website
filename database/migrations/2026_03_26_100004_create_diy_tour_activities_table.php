<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diy_tour_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('diy_tour_cities')->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('activity_name');
            $table->string('category', 50)->nullable(); // cultural, nature, food, romantic, shopping
            $table->decimal('duration_hours', 4, 2)->default(2.00);
            $table->boolean('is_included')->default(true);
            $table->decimal('cost_php', 10, 2)->default(0);
            $table->time('time_of_day')->nullable();
            $table->boolean('booking_required')->default(false);
            $table->timestamps();

            $table->index(['city_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diy_tour_activities');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diy_tour_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('diy_tour_sessions')->cascadeOnDelete();
            $table->string('tour_name')->nullable();
            $table->json('user_preferences')->nullable();
            $table->json('itinerary_data')->nullable();
            $table->json('map_data')->nullable();
            $table->json('pricing_data')->nullable();
            $table->json('validation_results')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->timestamps();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diy_tour_itineraries');
    }
};

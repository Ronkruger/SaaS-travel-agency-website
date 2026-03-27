<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diy_tour_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('diy_tour_itineraries')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('vote_type', ['city', 'activity', 'hotel_tier']);
            $table->unsignedBigInteger('item_id')->nullable();
            $table->enum('vote_value', ['yes', 'no', 'neutral']);
            $table->timestamps();

            $table->unique(['itinerary_id', 'user_id', 'vote_type', 'item_id'], 'diy_votes_unique');
            $table->index(['itinerary_id', 'vote_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diy_tour_votes');
    }
};

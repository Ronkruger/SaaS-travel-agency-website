<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description');
            $table->longText('description');
            $table->string('destination');
            $table->string('country');
            $table->string('featured_image');
            $table->decimal('price_per_person', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('duration_days');
            $table->integer('duration_nights');
            $table->integer('max_group_size');
            $table->integer('min_group_size')->default(1);
            $table->string('difficulty_level')->default('Easy'); // Easy, Moderate, Hard
            $table->string('language', 50)->default('English');
            $table->json('included_services')->nullable();  // What's included
            $table->json('excluded_services')->nullable();  // What's not included
            $table->json('itinerary')->nullable();          // Day-by-day plan
            $table->json('highlights')->nullable();         // Key highlights
            $table->string('departure_location')->nullable();
            $table->string('meeting_point')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->integer('total_bookings')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};

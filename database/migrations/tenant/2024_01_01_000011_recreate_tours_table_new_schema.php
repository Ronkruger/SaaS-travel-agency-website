<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('tours');
        Schema::enableForeignKeyConstraints();

        Schema::create('tours', function (Blueprint $table) {
            $table->id();

            // ── Basic Info ────────────────────────────────────────────────
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('line')->nullable();          // tour brand/series
            $table->string('continent')->nullable();
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->boolean('guaranteed_departure')->default(false);
            $table->string('booking_pdf_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('facebook_post_url')->nullable();

            // ── Pricing ───────────────────────────────────────────────────
            $table->decimal('regular_price_per_person', 10, 2)->nullable();
            $table->decimal('promo_price_per_person', 10, 2)->nullable();
            $table->decimal('base_price_per_day', 10, 2)->nullable();
            $table->boolean('is_sale_enabled')->default(false);
            $table->date('sale_end_date')->nullable();

            // ── Travel Details ────────────────────────────────────────────
            $table->json('travel_window')->nullable();   // {start, end}
            $table->json('departure_dates')->nullable(); // [{start,end,maxCapacity,currentBookings,isAvailable,price}]

            // ── Content ───────────────────────────────────────────────────
            $table->json('highlights')->nullable();
            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('related_images')->nullable();
            $table->string('video_file')->nullable();

            // ── Itinerary ─────────────────────────────────────────────────
            $table->json('itinerary')->nullable(); // [{day,title,description,image}]

            // ── Stops & Geography ─────────────────────────────────────────
            $table->json('full_stops')->nullable();      // [{city,country,days}]
            $table->json('additional_info')->nullable(); // {countriesVisited,startingPoint,endingPoint,mainCities,countries,citiesToVisit}

            // ── Booking ───────────────────────────────────────────────────
            $table->json('booking_links')->nullable();   // [{year,urls[]}]
            $table->boolean('allows_downpayment')->default(false);
            $table->decimal('fixed_downpayment_amount', 10, 2)->nullable();
            $table->unsignedSmallInteger('balance_due_days_before_travel')->nullable();

            // ── Optional Tours / Excursions ───────────────────────────────
            $table->json('optional_tours')->nullable();  // [{day,title,regularPrice,promoEnabled,promoType,promoValue,flipbookUrl}]

            // ── Cash Freebies ─────────────────────────────────────────────
            $table->json('cash_freebies')->nullable();   // [{label,type,value}]

            // ── Status & Metrics ──────────────────────────────────────────
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->decimal('average_rating', 3, 1)->default(0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('total_bookings')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};

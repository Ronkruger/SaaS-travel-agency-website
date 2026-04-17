<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique(); // e.g. DG-2024-000001
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('tour_schedules')->nullOnDelete();
            $table->date('tour_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            $table->integer('total_guests');
            $table->decimal('price_per_adult', 10, 2);
            $table->decimal('price_per_child', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('coupon_code')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'refunded'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->text('special_requests')->nullable();
            $table->json('traveler_details')->nullable(); // JSON array of traveler info
            // Contact info (may differ from account)
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

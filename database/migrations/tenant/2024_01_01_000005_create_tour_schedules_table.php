<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->date('departure_date');
            $table->date('return_date');
            $table->integer('available_seats');
            $table->integer('booked_seats')->default(0);
            $table->decimal('price_override', 10, 2)->nullable(); // Special pricing for specific dates
            $table->enum('status', ['active', 'sold_out', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_schedules');
    }
};

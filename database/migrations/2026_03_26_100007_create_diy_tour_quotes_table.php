<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diy_tour_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('diy_tour_itineraries')->cascadeOnDelete();
            $table->decimal('quoted_price_php', 10, 2);
            $table->date('valid_until')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('generated_by', 50)->default('ai_automatic'); // 'ai_automatic' or admin ID
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->timestamps();

            $table->index(['itinerary_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diy_tour_quotes');
    }
};

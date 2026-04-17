<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject')->nullable();
            $table->string('mail_class')->nullable();   // e.g. BookingConfirmationMail
            $table->string('status')->default('sent');  // sent | failed
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('booking_number')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['to_email', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_email_logs');
    }
};

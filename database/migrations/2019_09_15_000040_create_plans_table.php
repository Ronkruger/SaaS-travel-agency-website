<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();

            // Feature limits
            $table->integer('max_tours')->default(10);
            $table->integer('max_bookings_per_month')->default(50);
            $table->integer('max_admin_users')->default(2);
            $table->boolean('has_diy_builder')->default(false);
            $table->boolean('has_custom_domain')->default(false);
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_advanced_reports')->default(false);
            $table->boolean('has_email_marketing')->default(false);
            $table->boolean('has_priority_support')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

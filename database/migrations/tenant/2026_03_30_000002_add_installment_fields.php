<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add installment settings to tours
        Schema::table('tours', function (Blueprint $table) {
            $table->unsignedTinyInteger('installment_months')->nullable()->after('balance_due_days_before_travel')
                  ->comment('Max payment terms allowed (1-15 months)');
            $table->decimal('monthly_installment_amount', 10, 2)->nullable()->after('installment_months')
                  ->comment('Fixed monthly payment amount in PHP');
        });

        // Add installment tracking to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_method', ['xendit', 'cash', 'installment'])->default('xendit')->after('payment_status');
            $table->unsignedTinyInteger('installment_months')->nullable()->after('payment_method');
            $table->decimal('downpayment_amount', 10, 2)->nullable()->after('installment_months');
            $table->json('installment_schedule')->nullable()->after('downpayment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'installment_months', 'downpayment_amount', 'installment_schedule']);
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['installment_months', 'monthly_installment_amount']);
        });
    }
};

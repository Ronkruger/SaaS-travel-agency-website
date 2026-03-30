<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL stores Laravel enums as VARCHAR + CHECK constraint.
        // Drop the old constraint and add an updated one that includes 'installment'.
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_payment_method_check");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_payment_method_check CHECK (payment_method IN ('xendit', 'cash', 'installment'))");

        // Also ensure the column exists with the correct default (idempotent guard).
        // If the column was never created at all, add it now.
        if (!\Schema::hasColumn('bookings', 'payment_method')) {
            \Schema::table('bookings', function ($table) {
                $table->string('payment_method')->default('xendit')->after('payment_status');
                DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_payment_method_check CHECK (payment_method IN ('xendit', 'cash', 'installment'))");
            });
        }

        // Same guard for installment_months, downpayment_amount, installment_schedule
        if (!\Schema::hasColumn('bookings', 'installment_months')) {
            \Schema::table('bookings', function ($table) {
                $table->unsignedTinyInteger('installment_months')->nullable()->after('payment_method');
            });
        }
        if (!\Schema::hasColumn('bookings', 'downpayment_amount')) {
            \Schema::table('bookings', function ($table) {
                $table->decimal('downpayment_amount', 10, 2)->nullable()->after('installment_months');
            });
        }
        if (!\Schema::hasColumn('bookings', 'installment_schedule')) {
            \Schema::table('bookings', function ($table) {
                $table->json('installment_schedule')->nullable()->after('downpayment_amount');
            });
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_payment_method_check");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_payment_method_check CHECK (payment_method IN ('xendit', 'cash'))");
    }
};

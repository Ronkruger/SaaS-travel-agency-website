<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL: ENUM column — just MODIFY to expand the allowed values
            DB::statement("ALTER TABLE bookings MODIFY COLUMN payment_method ENUM('xendit', 'cash', 'installment') NOT NULL DEFAULT 'xendit'");
        } else {
            // PostgreSQL: VARCHAR + CHECK constraint
            DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_payment_method_check");
            DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_payment_method_check CHECK (payment_method IN ('xendit', 'cash', 'installment'))");
        }

        // Defensively add new columns if they're missing (idempotent)
        if (!Schema::hasColumn('bookings', 'installment_months')) {
            Schema::table('bookings', function ($table) {
                $table->unsignedTinyInteger('installment_months')->nullable()->after('payment_method');
            });
        }
        if (!Schema::hasColumn('bookings', 'downpayment_amount')) {
            Schema::table('bookings', function ($table) {
                $table->decimal('downpayment_amount', 10, 2)->nullable()->after('installment_months');
            });
        }
        if (!Schema::hasColumn('bookings', 'installment_schedule')) {
            Schema::table('bookings', function ($table) {
                $table->json('installment_schedule')->nullable()->after('downpayment_amount');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN payment_method ENUM('xendit', 'cash') NOT NULL DEFAULT 'xendit'");
        } else {
            DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_payment_method_check");
            DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_payment_method_check CHECK (payment_method IN ('xendit', 'cash'))");
        }
    }
};

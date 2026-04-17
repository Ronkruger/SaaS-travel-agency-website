<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Make booking_id nullable so payments can belong to either a booking OR a DIY quote
            $table->unsignedBigInteger('booking_id')->nullable()->change();

            // Add DIY quote foreign key
            $table->foreignId('diy_quote_id')
                  ->nullable()
                  ->after('booking_id')
                  ->constrained('diy_tour_quotes')
                  ->nullOnDelete();
        });

        // Add xendit_invoice_id to diy_tour_quotes for webhook matching
        Schema::table('diy_tour_quotes', function (Blueprint $table) {
            $table->string('xendit_invoice_id')->nullable()->after('status');
            $table->string('payment_type')->nullable()->after('xendit_invoice_id'); // 'per_person' or 'group'
            $table->unsignedSmallInteger('pax_count')->default(1)->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('diy_tour_quotes', function (Blueprint $table) {
            $table->dropColumn(['xendit_invoice_id', 'payment_type', 'pax_count']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['diy_quote_id']);
            $table->dropColumn('diy_quote_id');
        });
    }
};

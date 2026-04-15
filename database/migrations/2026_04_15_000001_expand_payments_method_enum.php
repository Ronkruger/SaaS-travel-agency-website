<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add xendit, installment, and manual to the payments.method enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('credit_card','debit_card','paypal','bank_transfer','cash','xendit','installment','manual') NOT NULL DEFAULT 'xendit'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('credit_card','debit_card','paypal','bank_transfer','cash') NOT NULL DEFAULT 'credit_card'");
    }
};

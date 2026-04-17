<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('admin_users')->cascadeOnDelete();
            $table->string('type', 30); // 'booking' or 'tour'
            $table->unsignedBigInteger('target_id');
            $table->string('target_label'); // e.g. booking number or tour title
            $table->text('reason');
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deletion_requests');
    }
};

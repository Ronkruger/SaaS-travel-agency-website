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
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->cascadeOnDelete(); // null = "all"
            $table->string('type', 50);         // new_booking | pending_review | etc.
            $table->string('title');
            $table->string('body');
            $table->string('url')->nullable();   // link to open on click
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['admin_user_id', 'is_read', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};

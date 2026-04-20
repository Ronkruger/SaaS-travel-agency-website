<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('gateway_name');
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_requests');
    }
};

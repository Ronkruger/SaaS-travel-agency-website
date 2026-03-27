<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diy_tour_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('diy_tour_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('permission_level', ['view', 'suggest', 'edit'])->default('view');
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->useCurrent();
            $table->timestamps();

            $table->unique(['session_id', 'user_id']);
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diy_tour_collaborators');
    }
};

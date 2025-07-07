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
        Schema::create('pomodoro_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('study_log_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('session_type', ['work', 'short_break', 'long_break'])->default('work');
            $table->integer('planned_duration'); // in minutes
            $table->integer('actual_duration')->nullable(); // in minutes - actual time spent
            $table->enum('status', ['active', 'completed', 'cancelled', 'paused'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Index for performance
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoro_sessions');
    }
};

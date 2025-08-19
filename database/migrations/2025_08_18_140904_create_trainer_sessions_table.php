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
        Schema::create('trainer_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_profile_id');
            $table->uuid('trainee_user_id');
            $table->uuid('trainee_membership_id');
            $table->date('session_date');
            $table->time('session_time');
            $table->integer('duration_minutes')->default(60);
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->decimal('fee_amount', 8, 2);
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('location', 200)->nullable();
            $table->text('notes')->nullable();
            $table->integer('trainee_rating')->nullable()->check('trainee_rating >= 1 AND trainee_rating <= 5');
            $table->text('trainee_feedback')->nullable();
            $table->text('trainer_notes')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('trainer_profile_id')->references('id')->on('trainer_profiles')->onDelete('cascade');
            $table->foreign('trainee_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('trainee_membership_id')->references('id')->on('memberships')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['trainer_profile_id']);
            $table->index(['trainee_user_id']);
            $table->index(['session_date']);
            $table->index(['status']);
            $table->index(['payment_status']);
            $table->index(['trainer_profile_id', 'session_date']);
            $table->index(['trainee_user_id', 'session_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_sessions');
    }
};

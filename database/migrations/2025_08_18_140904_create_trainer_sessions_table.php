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
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->decimal('fee_amount', 10, 2);
            $table->string('payment_status', 20)->default('pending');
            $table->string('location', 200)->nullable();
            $table->text('notes')->nullable();
            $table->integer('trainee_rating')->nullable();
            $table->text('trainee_feedback')->nullable();
            $table->text('trainer_notes')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Foreign key constraints
            $table->foreign('trainer_profile_id')->references('id')->on('trainer_profiles')->onDelete('cascade');
            $table->foreign('trainee_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('trainee_membership_id')->references('id')->on('memberships')->onDelete('cascade');

            // Indexes for performance
            $table->index('trainer_profile_id', 'idx_trainer_sessions_trainer');
            $table->index('trainee_user_id', 'idx_trainer_sessions_trainee');
            $table->index('session_date', 'idx_trainer_sessions_date');
            $table->index('status', 'idx_trainer_sessions_status');
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

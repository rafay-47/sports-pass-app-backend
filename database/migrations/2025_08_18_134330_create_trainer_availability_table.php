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
        Schema::create('trainer_availability', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_profile_id');
            $table->integer('day_of_week')->check('day_of_week >= 0 AND day_of_week <= 6'); // 0=Sunday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamp('created_at')->useCurrent();
            // Foreign key constraints
            $table->foreign('trainer_profile_id')->references('id')->on('trainer_profiles')->onDelete('cascade');
            // Unique constraint to prevent overlapping time slots
            $table->unique(['trainer_profile_id', 'day_of_week', 'start_time', 'end_time']);
            // Indexes for performance
            $table->index(['trainer_profile_id']);
            $table->index(['day_of_week']);
            $table->index(['trainer_profile_id', 'day_of_week', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_availability');
    }
};

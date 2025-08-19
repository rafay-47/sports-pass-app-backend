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
        Schema::create('trainer_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique(); // Make user_id globally unique
            $table->uuid('sport_id');
            $table->uuid('tier_id');
            $table->integer('experience_years');
            $table->text('bio');
            $table->decimal('hourly_rate', 10, 2)->nullable(); // Update precision
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_sessions')->default(0);
            $table->decimal('total_earnings', 10, 2)->default(0.00);
            $table->decimal('monthly_earnings', 10, 2)->default(0.00);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_available')->default(true);
            $table->enum('gender_preference', ['male', 'female', 'both'])->default('both');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('cascade');

            // Indexes for performance
            $table->index(['sport_id', 'is_verified', 'is_available']);
            $table->index(['user_id']);
            $table->index(['tier_id']);
            $table->index(['rating']);
            $table->index(['experience_years']);
            $table->index(['hourly_rate']);
            $table->index(['is_verified']);
            $table->index(['is_available']);
            $table->index(['gender_preference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_profiles');
    }
};

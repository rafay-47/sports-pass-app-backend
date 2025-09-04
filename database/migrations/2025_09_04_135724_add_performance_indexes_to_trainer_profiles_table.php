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
        Schema::table('trainer_profiles', function (Blueprint $table) {
            // Composite indexes for common queries
            $table->index(['sport_id', 'is_verified', 'is_available'], 'trainer_profiles_sport_verified_available_idx');
            $table->index(['tier_id', 'is_verified'], 'trainer_profiles_tier_verified_idx');
            $table->index(['user_id', 'is_verified'], 'trainer_profiles_user_verified_idx');
            
            // Partial indexes for better performance on filtered queries
            $table->index(['rating'], 'trainer_profiles_rating_idx')->where('rating', '>', 0);
            $table->index(['experience_years'], 'trainer_profiles_experience_idx')->where('experience_years', '>', 0);
            
            // Index for gender preference filtering
            $table->index(['gender_preference', 'is_available'], 'trainer_profiles_gender_available_idx');
            
            // Index for created_at to optimize recent trainer queries
            $table->index(['created_at'], 'trainer_profiles_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainer_profiles', function (Blueprint $table) {
            $table->dropIndex('trainer_profiles_sport_verified_available_idx');
            $table->dropIndex('trainer_profiles_tier_verified_idx');
            $table->dropIndex('trainer_profiles_user_verified_idx');
            $table->dropIndex('trainer_profiles_rating_idx');
            $table->dropIndex('trainer_profiles_experience_idx');
            $table->dropIndex('trainer_profiles_gender_available_idx');
            $table->dropIndex('trainer_profiles_created_at_idx');
        });
    }
};

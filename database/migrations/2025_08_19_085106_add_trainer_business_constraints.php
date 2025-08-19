<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add constraint to ensure trainer rating is within valid range
        DB::statement('
            ALTER TABLE trainer_profiles 
            ADD CONSTRAINT check_trainer_rating_range 
            CHECK (rating >= 0.0 AND rating <= 5.0)
        ');

        // Add constraint to ensure experience years is positive
        DB::statement('
            ALTER TABLE trainer_profiles 
            ADD CONSTRAINT check_experience_years_positive 
            CHECK (experience_years >= 0)
        ');

        // Add constraint to ensure hourly rate is positive when set
        DB::statement('
            ALTER TABLE trainer_profiles 
            ADD CONSTRAINT check_hourly_rate_positive 
            CHECK (hourly_rate IS NULL OR hourly_rate > 0)
        ');

        // Add constraint to ensure session duration is positive
        DB::statement('
            ALTER TABLE trainer_sessions 
            ADD CONSTRAINT check_session_duration_positive 
            CHECK (duration_minutes > 0)
        ');

        // Add constraint to ensure session fee is non-negative
        DB::statement('
            ALTER TABLE trainer_sessions 
            ADD CONSTRAINT check_session_fee_non_negative 
            CHECK (fee_amount >= 0)
        ');

        // Add constraint to ensure total earnings are non-negative
        DB::statement('
            ALTER TABLE trainer_profiles 
            ADD CONSTRAINT check_total_earnings_non_negative 
            CHECK (total_earnings >= 0)
        ');

        // Add constraint to ensure monthly earnings are non-negative
        DB::statement('
            ALTER TABLE trainer_profiles 
            ADD CONSTRAINT check_monthly_earnings_non_negative 
            CHECK (monthly_earnings >= 0)
        ');

        // Add constraint to ensure total sessions is non-negative
        DB::statement('
            ALTER TABLE trainer_profiles 
            ADD CONSTRAINT check_total_sessions_non_negative 
            CHECK (total_sessions >= 0)
        ');

        // Note: Complex constraints with subqueries are not supported in PostgreSQL CHECK constraints
        // These will be enforced at the application level through model validation and business logic
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraints in reverse order
        DB::statement('ALTER TABLE trainer_profiles DROP CONSTRAINT IF EXISTS check_total_sessions_non_negative');
        DB::statement('ALTER TABLE trainer_profiles DROP CONSTRAINT IF EXISTS check_monthly_earnings_non_negative');
        DB::statement('ALTER TABLE trainer_profiles DROP CONSTRAINT IF EXISTS check_total_earnings_non_negative');
        DB::statement('ALTER TABLE trainer_sessions DROP CONSTRAINT IF EXISTS check_session_fee_non_negative');
        DB::statement('ALTER TABLE trainer_sessions DROP CONSTRAINT IF EXISTS check_session_duration_positive');
        DB::statement('ALTER TABLE trainer_profiles DROP CONSTRAINT IF EXISTS check_hourly_rate_positive');
        DB::statement('ALTER TABLE trainer_profiles DROP CONSTRAINT IF EXISTS check_experience_years_positive');
        DB::statement('ALTER TABLE trainer_profiles DROP CONSTRAINT IF EXISTS check_trainer_rating_range');
    }
};

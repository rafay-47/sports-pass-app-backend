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
        Schema::table('trainer_availability', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique(['trainer_profile_id', 'day_of_week', 'start_time', 'end_time']);
            
            // Drop the column
            $table->dropColumn('day_of_week');
        });
        
        Schema::table('trainer_availability', function (Blueprint $table) {
            // Add the new column with enum
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])
                  ->after('trainer_profile_id');
            
            // Re-add the unique constraint
            $table->unique(['trainer_profile_id', 'day_of_week', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainer_availability', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique(['trainer_profile_id', 'day_of_week', 'start_time', 'end_time']);
            
            // Drop the column
            $table->dropColumn('day_of_week');
        });
        
        Schema::table('trainer_availability', function (Blueprint $table) {
            // Add back the integer column
            $table->integer('day_of_week')->after('trainer_profile_id');
            
            // Re-add the unique constraint
            $table->unique(['trainer_profile_id', 'day_of_week', 'start_time', 'end_time']);
        });
    }
};

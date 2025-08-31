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
            // Drop the index first
            $table->dropIndex(['hourly_rate']);
            // Then drop the column
            $table->dropColumn('hourly_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainer_profiles', function (Blueprint $table) {
            // Recreate the column
            $table->decimal('hourly_rate', 10, 2)->nullable();
            // Recreate the index
            $table->index(['hourly_rate']);
        });
    }
};

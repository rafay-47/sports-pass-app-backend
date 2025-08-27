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
        // Add custom_name to club_sports pivot table
        Schema::table('club_sports', function (Blueprint $table) {
            $table->string('custom_name', 255)->nullable()->after('sport_id');
        });

        // Add custom_name to club_amenities pivot table
        Schema::table('club_amenities', function (Blueprint $table) {
            $table->string('custom_name', 255)->nullable()->after('amenity_id');
        });

        // Add custom_name to club_facilities pivot table
        Schema::table('club_facilities', function (Blueprint $table) {
            $table->string('custom_name', 255)->nullable()->after('facility_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove custom_name from club_sports pivot table
        Schema::table('club_sports', function (Blueprint $table) {
            $table->dropColumn('custom_name');
        });

        // Remove custom_name from club_amenities pivot table
        Schema::table('club_amenities', function (Blueprint $table) {
            $table->dropColumn('custom_name');
        });

        // Remove custom_name from club_facilities pivot table
        Schema::table('club_facilities', function (Blueprint $table) {
            $table->dropColumn('custom_name');
        });
    }
};

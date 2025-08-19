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
        Schema::table('trainer_locations', function (Blueprint $table) {
            // Add missing columns
            $table->enum('location_type', ['gym', 'outdoor', 'home', 'client_location', 'online'])->default('gym')->after('location_name');
            $table->string('city', 100)->after('address');
            $table->string('area', 100)->nullable()->after('city');
            $table->boolean('is_primary')->default(false)->after('longitude');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainer_locations', function (Blueprint $table) {
            $table->dropColumn(['location_type', 'city', 'area', 'is_primary', 'updated_at']);
        });
    }
};

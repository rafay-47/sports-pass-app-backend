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
        Schema::table('events', function (Blueprint $table) {
            $table->enum('location_type', ['club', 'custom', 'legacy'])->default('legacy')->after('location');
        });

        // Migrate existing data
        DB::statement("
            UPDATE events
            SET location_type = CASE
                WHEN club_id IS NOT NULL THEN 'club'
                WHEN custom_address IS NOT NULL THEN 'custom'
                ELSE 'legacy'
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('location_type');
        });
    }
};

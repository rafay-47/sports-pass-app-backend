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
        Schema::table('clubs', function (Blueprint $table) {
            // Add the new sport_id column as nullable first
            $table->uuid('sport_id')->nullable()->after('name');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
            
            // Add index for the foreign key
            $table->index('sport_id', 'idx_clubs_sport');
        });
        
        // Get the first sport to use as default
        $defaultSport = DB::table('sports')->first();
        
        if ($defaultSport) {
            // Update existing clubs to use the first sport as default
            DB::table('clubs')->update(['sport_id' => $defaultSport->id]);
        }
        
        // Now make the column NOT NULL
        Schema::table('clubs', function (Blueprint $table) {
            $table->uuid('sport_id')->nullable(false)->change();
        });
        
        // Drop the old type column
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            // Add back the original type column
            $table->string('type', 100)->after('name');
            
            // Drop the foreign key and index
            $table->dropForeign(['sport_id']);
            $table->dropIndex('idx_clubs_sport');
            
            // Drop the sport_id column
            $table->dropColumn('sport_id');
        });
    }
};

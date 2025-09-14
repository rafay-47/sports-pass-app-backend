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
        Schema::table('events', function (Blueprint $table) {
            $table->uuid('club_id')->nullable()->after('sport_id');
            $table->string('custom_address')->nullable()->after('location');
            $table->string('custom_city')->nullable()->after('custom_address');
            $table->string('custom_state')->nullable()->after('custom_city');
            
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
            $table->index('club_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
            $table->dropIndex(['club_id']);
            $table->dropColumn(['club_id', 'custom_address', 'custom_city', 'custom_state']);
        });
    }
};

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
            $table->uuid('organizer_id')->nullable()->after('organizer');
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('set null');
            $table->index('organizer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->dropIndex(['organizer_id']);
            $table->dropColumn('organizer_id');
        });
    }
};

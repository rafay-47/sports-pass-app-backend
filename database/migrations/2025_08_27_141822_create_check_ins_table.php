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
        Schema::create('check_ins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('membership_id')->nullable();
            $table->uuid('club_id');
            $table->date('check_in_date');
            $table->datetime('check_in_time');
            $table->string('sport_type', 100)->nullable();
            $table->string('qr_code_used', 100)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('set null');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->index(['user_id', 'check_in_date']);
            $table->index(['club_id', 'check_in_date']);
            $table->index('check_in_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};

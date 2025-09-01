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
        Schema::create('trainer_clubs', function (Blueprint $table) {
            $table->id();
            $table->uuid('trainer_profile_id');
            $table->uuid('club_id');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('trainer_profile_id')->references('id')->on('trainer_profiles')->onDelete('cascade');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->unique(['trainer_profile_id', 'club_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_clubs');
    }
};

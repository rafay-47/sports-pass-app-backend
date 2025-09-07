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
        Schema::create('trainer_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('membership_id');
            $table->uuid('sport_id');
            $table->uuid('tier_id');
            $table->uuid('service_id');
            $table->enum('request_type', ['specific_trainer', 'open_request']);
            $table->uuid('trainer_profile_id')->nullable();
            $table->uuid('club_id')->nullable();
            $table->jsonb('preferred_time_slots')->nullable(); // Array of objects: [{"start": "10:00", "end": "11:00"}]
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->uuid('accepted_by_trainer_id')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('restrict');
            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('restrict');
            $table->foreign('service_id')->references('id')->on('sport_services')->onDelete('restrict');
            $table->foreign('trainer_profile_id')->references('id')->on('trainer_profiles')->onDelete('cascade');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('accepted_by_trainer_id')->references('id')->on('trainer_profiles')->onDelete('set null');

            $table->index(['user_id', 'status']);
            $table->index(['membership_id']);
            $table->index(['sport_id', 'tier_id']);
            $table->index(['trainer_profile_id']);
            $table->index(['club_id']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_requests');
    }
};

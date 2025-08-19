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
        Schema::create('trainer_specialties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_profile_id');
            $table->string('specialty', 100);
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key constraints
            $table->foreign('trainer_profile_id')->references('id')->on('trainer_profiles')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate specialties for same trainer
            $table->unique(['trainer_profile_id', 'specialty']);
            
            // Indexes for performance
            $table->index(['trainer_profile_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_specialties');
    }
};

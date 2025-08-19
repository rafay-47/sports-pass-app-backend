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
        Schema::create('trainer_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_profile_id');
            $table->string('location_name', 200);
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key constraints
            $table->foreign('trainer_profile_id')->references('id')->on('trainer_profiles')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['trainer_profile_id']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_locations');
    }
};

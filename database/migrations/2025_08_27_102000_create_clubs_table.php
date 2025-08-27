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
        Schema::create('clubs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_id');
            $table->string('name', 200);
            $table->string('type', 100);
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('city', 100)->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->decimal('rating', 3, 2)->default(0.0);
            $table->string('price_range', 20)->nullable(); // e.g., "$$", "$$$"
            $table->enum('category', ['male', 'female', 'mixed']);
            $table->string('qr_code', 100)->unique();
            $table->enum('status', ['active', 'pending', 'suspended'])->default('active');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->jsonb('timings')->nullable(); // e.g. { "monday": {"open":"06:00","close":"22:00","isOpen":true}, ... }
            $table->jsonb('pricing')->nullable(); // e.g. { "basic":1000, "standard":2000, "premium":3000 }
            $table->boolean('is_active')->default(true); // kept for compatibility
            $table->timestamps();

            // Foreign key to users
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('owner_id', 'idx_clubs_owner');
            $table->index('qr_code', 'idx_clubs_qr_code');
            $table->index(['latitude', 'longitude'], 'idx_clubs_location');
            $table->index('category', 'idx_clubs_category');
            $table->index('status', 'idx_clubs_status');
            $table->index('verification_status', 'idx_clubs_verification');
            $table->index('is_active', 'idx_clubs_active');
            $table->index('rating', 'idx_clubs_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};

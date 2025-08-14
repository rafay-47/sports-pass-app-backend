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
        Schema::create('sport_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sport_id');
            $table->string('service_name', 100);
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
            
            // Indexes
            $table->index('sport_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sport_services');
    }
};

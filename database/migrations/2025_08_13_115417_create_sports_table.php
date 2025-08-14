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
        Schema::create('sports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('display_name', 100);
            $table->string('icon', 255);
            $table->string('color', 7); // Hex color code
            $table->text('description')->nullable();
            $table->integer('number_of_services')->default(0); // Count of services available for this sport
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('is_active', 'idx_sports_active');
            $table->index('number_of_services', 'idx_sports_services_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sports');
    }
};

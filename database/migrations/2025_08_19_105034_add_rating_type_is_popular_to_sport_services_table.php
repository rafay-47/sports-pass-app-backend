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
        Schema::table('sport_services', function (Blueprint $table) {
            // Add rating field (0.0 to 5.0)
            $table->decimal('rating', 3, 2)->default(0.0)->after('discount_percentage');
            
            // Add type field to differentiate between trainers and other services
            $table->enum('type', ['trainer', 'facility', 'equipment', 'class', 'consultation', 'other'])
                  ->default('other')
                  ->after('rating');
            
            // Add is_popular field
            $table->boolean('is_popular')->default(false)->after('type');
            
            // Add indexes for performance
            $table->index(['rating']);
            $table->index(['type']);
            $table->index(['is_popular']);
            $table->index(['type', 'is_popular']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sport_services', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['type', 'is_popular']);
            $table->dropIndex(['is_popular']);
            $table->dropIndex(['type']);
            $table->dropIndex(['rating']);
            
            // Drop columns
            $table->dropColumn(['is_popular', 'type', 'rating']);
        });
    }
};

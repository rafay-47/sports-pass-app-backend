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
        Schema::create('service_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('membership_id');
            $table->uuid('sport_service_id');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['completed', 'cancelled', 'upcoming', 'expired'])->default('completed');
            $table->date('service_date')->nullable();
            $table->time('service_time')->nullable();
            $table->string('provider', 200)->nullable();
            $table->string('location', 200)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
            $table->foreign('sport_service_id')->references('id')->on('sport_services')->onDelete('restrict');

            // Indexes
            $table->index(['user_id']);
            $table->index(['membership_id']);
            $table->index(['sport_service_id']);
            $table->index(['status']);
            $table->index(['service_date']);
            $table->index(['user_id', 'status']);
            $table->index(['membership_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_purchases');
    }
};

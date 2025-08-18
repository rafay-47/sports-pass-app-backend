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
        Schema::create('memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('membership_number', 50)->unique();
            $table->uuid('user_id');
            $table->uuid('sport_id');
            $table->uuid('tier_id');
            $table->enum('status', ['active', 'paused', 'expired', 'cancelled'])->default('active');
            $table->date('purchase_date');
            $table->date('start_date');
            $table->date('expiry_date');
            $table->boolean('auto_renew')->default(true);
            $table->decimal('purchase_amount', 10, 2);
            $table->integer('monthly_check_ins')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->decimal('monthly_spent', 10, 2)->default(0);
            $table->decimal('total_earnings', 10, 2)->default(0); // For trainers
            $table->decimal('monthly_earnings', 10, 2)->default(0); // For trainers
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('restrict');
            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('restrict');

            // Indexes
            $table->index(['user_id']);
            $table->index(['sport_id']);
            $table->index(['tier_id']);
            $table->index(['membership_number']);
            $table->index(['status']);
            $table->index(['expiry_date']);
            $table->index(['user_id', 'sport_id', 'status']);
            $table->index(['user_id', 'tier_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};

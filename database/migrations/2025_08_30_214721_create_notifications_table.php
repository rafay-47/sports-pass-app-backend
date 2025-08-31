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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('title', 200);
            $table->text('message');
            $table->enum('type', ['info', 'success', 'warning', 'error', 'membership', 'event', 'trainer', 'checkin', 'payment']);
            $table->boolean('is_read')->default(false);
            $table->string('action_url', 500)->nullable();
            $table->jsonb('metadata')->nullable(); // Additional data for the notification
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['user_id']);
            $table->index(['user_id', 'is_read']);
            $table->index(['type']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

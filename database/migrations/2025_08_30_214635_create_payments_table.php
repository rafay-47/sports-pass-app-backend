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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('transaction_id', 100)->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('PKR');
            $table->string('payment_method', 50); // 'easypaisa', 'jazzcash', 'sadapay', 'bank', 'mastercard'
            $table->string('payment_type', 50); // 'membership', 'service', 'event', 'trainer_session'
            $table->uuid('reference_id')->nullable(); // ID of the related record (membership, service, etc.)
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->jsonb('payment_gateway_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamp('refund_date')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['user_id']);
            $table->index(['transaction_id']);
            $table->index(['status']);
            $table->index(['payment_type']);
            $table->index(['payment_date']);
            $table->index(['reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

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
        Schema::create('trainer_certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_profile_id');
            $table->string('certification_name', 200);
            $table->string('issuing_organization', 200)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('certificate_url', 500)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key constraints
            $table->foreign('trainer_profile_id')->references('id')->on('trainer_profiles')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['trainer_profile_id']);
            $table->index(['is_verified']);
            $table->index(['expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_certifications');
    }
};

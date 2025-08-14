<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->string('name', 255);
            $table->string('phone', 20)->unique();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('profile_image_url', 500)->nullable();
            $table->enum('user_role', ['member', 'owner', 'admin'])->default('member');
            $table->boolean('is_trainer')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('join_date')->default(DB::raw('CURRENT_DATE'));
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('email', 'idx_users_email');
            $table->index('phone', 'idx_users_phone');
            $table->index('join_date', 'idx_users_join_date');
            $table->index('is_trainer', 'idx_users_is_trainer');
            $table->index('user_role', 'idx_users_role');
            $table->index(['user_role', 'is_active'], 'idx_users_role_active');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('sport_id');
            $table->date('event_date');
            $table->datetime('event_time');
            $table->date('end_date')->nullable();
            $table->datetime('end_time')->nullable();
            $table->string('type')->default('tournament'); // tournament, workshop, class, competition
            $table->string('category')->nullable(); // beginner, intermediate, advanced
            $table->string('difficulty')->nullable(); // easy, medium, hard
            $table->decimal('fee', 8, 2)->default(0);
            $table->integer('max_participants')->default(50);
            $table->integer('current_participants')->default(0);
            $table->string('location')->nullable();
            $table->string('organizer')->nullable();
            $table->jsonb('requirements')->nullable();
            $table->jsonb('prizes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->datetime('registration_deadline')->nullable();
            $table->timestamps();

            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
            $table->index(['event_date', 'is_active']);
            $table->index(['sport_id', 'event_date']);
            $table->index('type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

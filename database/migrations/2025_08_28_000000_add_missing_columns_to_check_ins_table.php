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
        Schema::table('check_ins', function (Blueprint $table) {
            $table->datetime('check_out_time')->nullable()->after('check_in_time');
            $table->string('location', 255)->nullable()->after('qr_code_used');
            $table->enum('check_in_method', ['manual', 'qr_code', 'app'])->default('manual')->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropColumn(['check_out_time', 'location', 'check_in_method']);
        });
    }
};

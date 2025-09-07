<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'cancelled' to the existing enum
        DB::statement("ALTER TYPE trainer_requests_status_enum ADD VALUE 'cancelled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PostgreSQL doesn't support removing enum values easily
        // This would require complex recreation of the enum
        // For now, we'll leave this as is
    }
};

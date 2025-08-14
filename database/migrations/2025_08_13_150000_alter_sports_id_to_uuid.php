<?php

use Illuminate\Database\Migrations\Migration;

// This migration is now intentionally a no-op because the sports table is created
// directly with a UUID primary key in the earlier migration 2025_08_13_115417_create_sports_table.
// Keeping this file (emptied) avoids errors if it has already been recorded in version control.

return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};

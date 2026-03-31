<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Converts nd_state from a 3-value enum to a plain string column.
// The old enum only allowed ND_TRG, ND_PHG, ND_KEL — too restrictive.
// Validation is handled at the Form Request level, so no DB constraint needed.
// On MySQL (production): MODIFY COLUMN drops the enum and uses varchar, preserving data.
// On SQLite (local): run migrate:fresh — no safe alter path for SQLite dev.
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE projects MODIFY COLUMN nd_state VARCHAR(50) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE projects ALTER COLUMN nd_state TYPE VARCHAR(50)');
        }
        // SQLite: run migrate:fresh locally — no safe alter path needed for dev
    }

    public function down(): void
    {
        // Intentionally not reverting — restoring a 3-value enum would break existing data
    }
};

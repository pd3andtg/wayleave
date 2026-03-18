<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// SQLite cannot ALTER a CHECK constraint, so we recreate the table with
// updated pbt_name values using spaces instead of underscores (e.g. 'JKR HT').
// Existing rows are migrated, replacing underscores with spaces in pbt_name.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement("
            CREATE TABLE wayleave_pbts_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
                pbt_number TEXT NOT NULL CHECK(pbt_number IN ('PBT1','PBT2','PBT3')),
                pbt_name TEXT NOT NULL CHECK(pbt_name IN ('MBKT','MPK','MDS','MDB','MPD','JKR HT','JKR KN','JKR DN','JKR KT','JKR KM','JKR ST','Others')),
                pbt_name_other TEXT,
                wayleave_file TEXT,
                wayleave_received_date TEXT,
                endorsed_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
                created_at TEXT,
                updated_at TEXT
            )
        ");

        // Copy existing rows, converting 'JKR_XX' -> 'JKR XX' in pbt_name
        DB::statement("
            INSERT INTO wayleave_pbts_new
            SELECT id, project_id, pbt_number,
                   REPLACE(pbt_name, '_', ' '),
                   pbt_name_other, wayleave_file, wayleave_received_date,
                   endorsed_by, created_at, updated_at
            FROM wayleave_pbts
        ");

        DB::statement('DROP TABLE wayleave_pbts');
        DB::statement('ALTER TABLE wayleave_pbts_new RENAME TO wayleave_pbts');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement("
            CREATE TABLE wayleave_pbts_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
                pbt_number TEXT NOT NULL CHECK(pbt_number IN ('PBT1','PBT2','PBT3')),
                pbt_name TEXT NOT NULL CHECK(pbt_name IN ('MBKT','MPK','MDS','MDB','MPD','JKR_HT','JKR_KN','JKR_DN','JKR_KT','JKR_KM','JKR_ST','Others')),
                pbt_name_other TEXT,
                wayleave_file TEXT,
                wayleave_received_date TEXT,
                endorsed_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
                created_at TEXT,
                updated_at TEXT
            )
        ");

        DB::statement("
            INSERT INTO wayleave_pbts_new
            SELECT id, project_id, pbt_number,
                   REPLACE(pbt_name, ' ', '_'),
                   pbt_name_other, wayleave_file, wayleave_received_date,
                   endorsed_by, created_at, updated_at
            FROM wayleave_pbts
        ");

        DB::statement('DROP TABLE wayleave_pbts');
        DB::statement('ALTER TABLE wayleave_pbts_new RENAME TO wayleave_pbts');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};

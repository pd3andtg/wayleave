<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update any existing rows with underscores to spaces
        DB::statement("UPDATE wayleave_pbts SET pbt_name = REPLACE(pbt_name, '_', ' ')");

        // Modify the enum column to use space-separated values
        DB::statement("
            ALTER TABLE wayleave_pbts
            MODIFY COLUMN pbt_name ENUM('MBKT','MPK','MDS','MDB','MPD','JKR HT','JKR KN','JKR DN','JKR KT','JKR KM','JKR ST','Others') NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("UPDATE wayleave_pbts SET pbt_name = REPLACE(pbt_name, ' ', '_')");

        DB::statement("
            ALTER TABLE wayleave_pbts
            MODIFY COLUMN pbt_name ENUM('MBKT','MPK','MDS','MDB','MPD','JKR_HT','JKR_KN','JKR_DN','JKR_KT','JKR_KM','JKR_ST','Others') NOT NULL
        ");
    }
};
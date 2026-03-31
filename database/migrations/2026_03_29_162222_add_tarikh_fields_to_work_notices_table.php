<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds tarikh_mula_kerja and tarikh_siap_kerja date fields to work_notices.
// These capture the actual work start/end dates (separate from the file upload timestamp)
// and are displayed on the project timeline for Sections 10 and 11.
return new class extends Migration
{
    public function up(): void
    {
        // No-op: tarikh_mula_kerja and tarikh_siap_kerja already defined in the create migration.
    }

    public function down(): void
    {
        // No-op.
    }
};

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
        Schema::table('work_notices', function (Blueprint $table) {
            $table->date('tarikh_mula_kerja')->nullable()->after('notis_mula_file');
            $table->date('tarikh_siap_kerja')->nullable()->after('notis_siap_file');
        });
    }

    public function down(): void
    {
        Schema::table('work_notices', function (Blueprint $table) {
            $table->dropColumn(['tarikh_mula_kerja', 'tarikh_siap_kerja']);
        });
    }
};

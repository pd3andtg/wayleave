<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds the endorsed_file column to bq_endorsements (Step 5 — officer section).
// Nullable so officers can save endorsement metadata without uploading a file.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bq_endorsements', function (Blueprint $table) {
            $table->string('endorsed_file')->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('bq_endorsements', function (Blueprint $table) {
            $table->dropColumn('endorsed_file');
        });
    }
};

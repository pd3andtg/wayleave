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
        Schema::table('wayleave_pbts', function (Blueprint $table) {
            // Date the officer endorsed this wayleave PBT — filled in Section 5 only.
            $table->date('endorsed_date')->nullable()->after('endorsed_by');
        });
    }

    public function down(): void
    {
        Schema::table('wayleave_pbts', function (Blueprint $table) {
            $table->dropColumn('endorsed_date');
        });
    }
};

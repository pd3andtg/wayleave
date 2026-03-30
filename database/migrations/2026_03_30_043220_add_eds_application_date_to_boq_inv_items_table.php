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
        Schema::table('boq_inv_items', function (Blueprint $table) {
            // Date the EDS number was issued — filled by officer/admin in Section 3 alongside eds_no.
            $table->date('eds_application_date')->nullable()->after('eds_no');
        });
    }

    public function down(): void
    {
        Schema::table('boq_inv_items', function (Blueprint $table) {
            $table->dropColumn('eds_application_date');
        });
    }
};

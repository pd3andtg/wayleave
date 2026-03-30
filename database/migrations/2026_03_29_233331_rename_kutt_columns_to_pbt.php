<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renames two columns that previously referenced KUTT to use PBT instead,
// to be consistent with the updated terminology across the system.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('payment_to_kutt', 'payment_to_pbt');
        });

        Schema::table('cpc_applications', function (Blueprint $table) {
            $table->renameColumn('date_submit_to_kutt', 'date_submit_to_pbt');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('payment_to_pbt', 'payment_to_kutt');
        });

        Schema::table('cpc_applications', function (Blueprint $table) {
            $table->renameColumn('date_submit_to_pbt', 'date_submit_to_kutt');
        });
    }
};

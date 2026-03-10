<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// bq_inv_file and uploaded_by were originally NOT NULL but need to be nullable
// because an officer can save endorsement data before the contractor uploads a file,
// and both fields coexist independently on the same row.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bq_invs', function (Blueprint $table) {
            $table->string('bq_inv_file')->nullable()->change();
            $table->foreignId('uploaded_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bq_invs', function (Blueprint $table) {
            $table->string('bq_inv_file')->nullable(false)->change();
            $table->foreignId('uploaded_by')->nullable(false)->change();
        });
    }
};

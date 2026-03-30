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
        Schema::table('permits_received', function (Blueprint $table) {
            // Free-text notes per permit record, e.g. PBT name or any remarks.
            $table->text('remarks')->nullable()->after('permit_file');
        });
    }

    public function down(): void
    {
        Schema::table('permits_received', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};

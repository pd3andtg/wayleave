<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// inv_payments columns were originally NOT NULL but need to be nullable
// because officers may fill in only some INV rows, leaving others blank.
// PostgreSQL does not support inline CHECK on ALTER COLUMN, so payment_status
// is handled via raw SQL while the other columns use Blueprint->change().
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_payments', function (Blueprint $table) {
            $table->string('eds_no')->nullable()->change();
            $table->date('date')->nullable()->change();
            $table->decimal('amount', 10, 2)->nullable()->change();
        });

        // PostgreSQL: drop NOT NULL on the enum column separately.
        DB::statement('ALTER TABLE inv_payments ALTER COLUMN payment_status DROP NOT NULL');
    }

    public function down(): void
    {
        Schema::table('inv_payments', function (Blueprint $table) {
            $table->string('eds_no')->nullable(false)->change();
            $table->date('date')->nullable(false)->change();
            $table->decimal('amount', 10, 2)->nullable(false)->change();
        });

        DB::statement('ALTER TABLE inv_payments ALTER COLUMN payment_status SET NOT NULL');
    }
};

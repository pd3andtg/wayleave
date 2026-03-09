<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds project-specific columns to the users table.
// Done as a separate migration (after units and companies exist)
// so that foreign key constraints on unit_id and company_id can be applied.
// id_number stores either staff_id (officers) or ic_no (contractors).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_number')->nullable()->after('password');
            $table->foreignId('unit_id')->nullable()->after('id_number')->constrained('units')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->after('unit_id')->constrained('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['company_id']);
            $table->dropColumn(['id_number', 'unit_id', 'company_id']);
        });
    }
};

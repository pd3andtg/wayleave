<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds a status column to the users table.
// New sign-ups start as 'pending' and must be approved by an Admin or Officer
// before they can log in. Seeded test accounts are set to 'approved' directly.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

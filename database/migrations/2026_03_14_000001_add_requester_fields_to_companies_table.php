<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds requester_name and requester_email to companies table.
// These fields capture the contact details of the person who submitted
// the company registration request, so admin can send approval/rejection emails.
// requester_by (FK to users) is kept as-is but may remain null since
// the requester has no account yet at the time of registration.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('requester_name')->nullable()->after('status');
            $table->string('requester_email')->nullable()->after('requester_name');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['requester_name', 'requester_email']);
        });
    }
};

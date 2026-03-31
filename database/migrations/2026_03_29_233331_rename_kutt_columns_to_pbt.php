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
        // No-op: payment_to_pbt and date_submit_to_pbt already use final names in create migrations.
    }

    public function down(): void
    {
        // No-op.
    }
};

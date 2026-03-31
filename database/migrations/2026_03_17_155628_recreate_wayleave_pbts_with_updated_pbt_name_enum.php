<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: pbt_name is now a plain string column (defined in the create migration).
        // Previously attempted MODIFY COLUMN for enum change — not needed with string type.
    }

    public function down(): void
    {
        // No-op.
    }
};
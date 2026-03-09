<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the bq_invs table (Step 2 & 3 of the project flow).
// Contractor uploads the BQ/INV file (Step 2).
// Officer endorses it and sets payment status (Step 3).
// payment_status is nullable until the officer endorses.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bq_invs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('bq_inv_file');
            $table->string('endorsed_file')->nullable();
            $table->enum('payment_status', ['waived', 'charged'])->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('endorsed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bq_invs');
    }
};

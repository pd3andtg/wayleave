<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the inv_endorsements table (Step 5 — officer section).
// Officer endorses each INV-type file from bq_inv_files.
// Mirrors bq_endorsements but adds amount, eds_no, and payment_status.
// payment_status has 3 options: paid, outstanding, waived.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bq_inv_file_id')->constrained('bq_inv_files')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('document_info');
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_status', ['paid', 'outstanding', 'waived']);
            $table->string('eds_no');
            $table->text('remarks')->nullable();
            $table->foreignId('endorsed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_endorsements');
    }
};

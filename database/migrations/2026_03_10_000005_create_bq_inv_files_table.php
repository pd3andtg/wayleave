<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the bq_inv_files table (Step 4 of the project flow — contractor section).
// Supports up to 6 files per project. Each file has its own metadata:
// document_info, payment_type (BQ or INV), date, amount, eds_no, remarks.
// file_number (1–6) identifies which slot the file belongs to.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bq_inv_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('file_number'); // 1–6, identifies the file slot
            $table->string('file_path');
            $table->string('document_info');
            $table->enum('payment_type', ['BQ', 'INV']);
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('eds_no');
            $table->text('remarks')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bq_inv_files');
    }
};

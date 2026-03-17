<?php

// Shared table for BOTH Section 2 (BOQ/INV Files — contractor) and
// Section 3 (TM BOQ/Invoice Endorsement — officer/admin).
//
// Section 2 shows: document_info, type, date_received, amount, remarks, updated_by, updated_at
// Section 3 shows: all Section 2 columns PLUS file_path, eds_no, payment_status, endorsed file upload per row
//
// file_path is a shared column: contractor uploads first, officer/admin can overwrite per row.
// endorsed_by is set when officer uploads the endorsed file in Section 3.
// "Add New BOQ/INV" button creates rows visible in both sections simultaneously.
//
// Replaces: bq_inv_files, bq_endorsements, inv_endorsements tables.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_inv_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('document_info');
            $table->enum('type', ['BQ', 'INV']);
            $table->date('date_received');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('file_path')->nullable();     // Shared: contractor uploads, officer can overwrite
            $table->string('eds_no')->nullable();        // Section 3 only — officer/admin fills
            $table->enum('payment_status', [             // Section 3 only — officer/admin fills
                'endorsed',
                'endorsed_and_paid',
                'pending_endorsement',
                'waived',
                'cancelled',
            ])->nullable();
            $table->foreignId('endorsed_by')->nullable()->constrained('users')->nullOnDelete(); // Set when officer uploads endorsed file
            $table->text('remarks')->nullable();         // Hint: BOQ/INV Number
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); // Tracks last editor
            $table->timestamps();                        // updated_at serves as date_updated in Section 2
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_inv_items');
    }
};

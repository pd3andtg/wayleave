<?php

// Creates the wayleave_payments table.
// Shared table for BOTH Section 6 (TM: Wayleave Payment Details) and
// Section 7 (TM: BG & BD Received from FINSSO).
//
// Section 6: Two rows per PBT — one FI row and one Deposit row.
//            Columns: payment_type, status, amount, eds_no, method_of_payment, application_date
// Section 7: Same rows, but only shows those where status = required.
//            Additional columns: received_posted_date, bg_bd_file_path (officer/admin only)
//
// method_of_payment replaces old deposit_payment_type and now applies to both FI and Deposit.
// New options: BG, BD_DAP, EFT_DAP

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wayleave_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('wayleave_pbt_id')->constrained('wayleave_pbts')->cascadeOnDelete();
            $table->enum('payment_type', ['FI', 'Deposit']); // One row per payment type per PBT
            $table->enum('status', ['required', 'not_required', 'waived'])->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('eds_no')->nullable();
            $table->enum('method_of_payment', ['BG', 'BD_DAP', 'EFT_DAP'])->nullable();
            $table->date('application_date')->nullable();    // fi_application_date or deposit_application_date
            // Section 7 only — officer/admin fills
            $table->date('received_posted_date')->nullable();
            $table->string('bg_bd_file_path')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayleave_payments');
    }
};

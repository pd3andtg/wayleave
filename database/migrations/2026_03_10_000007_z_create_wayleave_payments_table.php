<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the wayleave_payments table (Step 7 of the project flow — officer section).
// Officer records FI and deposit payment details per PBT after wayleave endorsement.
// Separated from wayleave_pbts so Step 6 (file/endorsement) and
// Step 7 (payment tracking) have distinct completion boundaries.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wayleave_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('wayleave_pbt_id')->constrained('wayleave_pbts')->cascadeOnDelete();
            $table->enum('fi_payment', ['required', 'not_required', 'waived'])->nullable();
            $table->string('fi_eds_no')->nullable();
            $table->date('fi_application_date')->nullable();
            $table->enum('deposit_payment', ['required', 'not_required', 'waived'])->nullable();
            $table->string('deposit_eds_no')->nullable();
            $table->enum('deposit_payment_type', ['BG', 'BD'])->nullable();
            $table->date('deposit_application_date')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayleave_payments');
    }
};

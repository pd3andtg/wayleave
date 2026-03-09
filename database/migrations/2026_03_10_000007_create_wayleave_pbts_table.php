<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the wayleave_pbts table (Steps 4 & 5 of the project flow).
// Contractor uploads the wayleave received from KUTT/PBT (Step 4).
// Officer endorses it and handles FI and deposit payments per PBT (Step 5).
// Up to 3 PBTs allowed per project (PBT1, PBT2, PBT3).
// pbt_name_other is required only when pbt_name is set to 'Others' —
// the contractor writes the PBT name themselves in that case.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wayleave_pbts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('pbt_number', ['PBT1', 'PBT2', 'PBT3']);
            $table->enum('pbt_name', [
                'MBKT', 'MPK', 'MDS', 'MDB', 'MPD',
                'JKR_HT', 'JKR_KN', 'JKR_DN', 'JKR_KT', 'JKR_KM', 'JKR_ST',
                'Others',
            ]);
            $table->string('pbt_name_other')->nullable();
            $table->string('wayleave_file');
            $table->date('wayleave_received_date');
            $table->string('endorsed_file')->nullable();
            $table->enum('fi_payment', ['required', 'not_required', 'waived'])->nullable();
            $table->string('fi_eds_no')->nullable();
            $table->date('fi_date')->nullable();
            $table->enum('deposit_payment', ['required', 'not_required', 'waived'])->nullable();
            $table->string('deposit_eds_no')->nullable();
            $table->enum('deposit_payment_type', ['BG', 'BD'])->nullable();
            $table->date('deposit_date')->nullable();
            $table->foreignId('endorsed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayleave_pbts');
    }
};

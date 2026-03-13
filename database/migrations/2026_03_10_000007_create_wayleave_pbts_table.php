<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the wayleave_pbts table (Step 6 of the project flow).
// Contractor uploads the wayleave file received from KUTT/PBT.
// Officer then overwrites the same wayleave_file column with the endorsed version
// and sets endorsed_by + endorsement_remarks automatically on upload.
// Up to 3 PBTs per project (PBT1, PBT2, PBT3).
// pbt_name_other is required only when pbt_name is set to 'Others'.
// FI and deposit payment details are stored separately in wayleave_payments (Step 7).
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
            // Shared file column: contractor uploads first, officer overwrites with endorsed version.
            $table->string('wayleave_file');
            $table->date('wayleave_received_date');
            // Set automatically to "Endorsed" when officer overwrites the file.
            $table->text('endorsement_remarks')->nullable();
            $table->foreignId('endorsed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayleave_pbts');
    }
};

<?php

// Creates the wayleave_pbts table.
// Section 4: Contractor uploads wayleave file per PBT.
// Section 5: Officer overwrites the same wayleave_file column and sets endorsed_by.
//            No other fields in Section 5 besides file upload and endorsed_by.
// endorsed_by is displayed in BOTH Section 4 and Section 5.
// Up to 3 PBTs per project (PBT1, PBT2, PBT3).
// pbt_name_other is required only when pbt_name = 'Others'.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
                'JKR HT', 'JKR KN', 'JKR DN', 'JKR KT', 'JKR KM', 'JKR ST',
                'Others',
            ]);
            $table->string('pbt_name_other')->nullable();
            // Shared file column: contractor uploads first (Section 4),
            // officer overwrites with endorsed version (Section 5).
            $table->string('wayleave_file')->nullable();
            $table->date('wayleave_received_date')->nullable();
            // Set when officer uploads in Section 5. Shown in both Section 4 and Section 5.
            $table->foreignId('endorsed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayleave_pbts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the work_notices table (Step 8 of the project flow).
// Contractor uploads Notis Mula Kerja, Notis Siap Kerja, and a combined
// site photos PDF (gambar sebelum, semasa, dan selepas) — one PDF only.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('notis_mula_file');
            $table->string('notis_siap_file');
            $table->string('gambar_file');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_notices');
    }
};

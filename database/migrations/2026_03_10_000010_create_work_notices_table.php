<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the work_notices table (Step 10 of the project flow — contractor section).
// Contractor uploads Notis Mula Kerja and Notis Siap Kerja.
// Gambar (site photos) has been removed entirely from the system.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('notis_mula_file')->nullable();
            $table->date('tarikh_mula_kerja')->nullable();
            $table->string('notis_siap_file')->nullable();
            $table->date('tarikh_siap_kerja')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_notices');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the cpc_applications table (Step 9 of the project flow).
// Contractor uploads the four documents required to apply for CPC
// and records the date submitted to KUTT.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpc_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('surat_serahan_file')->nullable();
            $table->string('laporan_bergambar_file')->nullable();
            $table->string('salinan_coa_file')->nullable();
            $table->string('salinan_permit_file')->nullable();
            $table->date('date_submit_to_kutt')->nullable();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpc_applications');
    }
};

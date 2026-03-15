<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the bq_endorsements table (Step 5 — officer section).
// Officer endorses each BQ-type file from bq_inv_files.
// Each endorsement records document_info, date, and optional remarks.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bq_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bq_inv_file_id')->constrained('bq_inv_files')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('document_info');
            $table->date('date');
            $table->text('remarks')->nullable();
            $table->string('endorsed_file')->nullable();
            $table->foreignId('endorsed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bq_endorsements');
    }
};

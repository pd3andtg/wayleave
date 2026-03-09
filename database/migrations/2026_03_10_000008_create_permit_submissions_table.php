<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the permit_submissions table (Step 6 of the project flow).
// Contractor submits the doc permit to KUTT and uploads the file
// with the PBT cop received as proof of submission.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('submit_date');
            $table->string('submission_file');
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_submissions');
    }
};

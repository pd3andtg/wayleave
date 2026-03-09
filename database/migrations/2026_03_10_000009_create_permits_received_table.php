<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the permits_received table (Step 7 of the project flow).
// Contractor records the date the permit was received and uploads the permit file.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permits_received', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('permit_received_date');
            $table->string('permit_file');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permits_received');
    }
};

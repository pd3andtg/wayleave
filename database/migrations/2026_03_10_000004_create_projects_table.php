<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the projects table.
// Each project belongs to one company and is registered by one user (contractor).
// nd_state determines which TM Tech division handles the project.
// status starts as 'outstanding' and changes to 'completed' only when
// the contractor uploads the CPC in Step 10.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->nullable();
            $table->string('lor_no')->nullable();
            $table->string('project_no')->nullable();
            $table->text('project_desc');
            $table->enum('nd_state', ['ND_TRG', 'ND_PHG', 'ND_KEL']);
            $table->text('remarks')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['outstanding', 'completed'])->default('outstanding');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

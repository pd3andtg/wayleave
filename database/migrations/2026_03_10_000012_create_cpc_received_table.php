<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the cpc_received table (Step 10 of the project flow).
// Contractor uploads the received CPC document.
// This is the final step — creating this record triggers the project
// status to change to 'completed' (handled in CpcReceivedService).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpc_received', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('cpc_file');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpc_received');
    }
};

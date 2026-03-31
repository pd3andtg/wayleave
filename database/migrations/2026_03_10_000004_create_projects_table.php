<?php

// Creates the projects table.
// Each project belongs to one company and is registered by one user.
// nd_state determines which TM Tech division handles the project.
// node_id references the TM Node (Admin manages via UI).
// pic_name stored as text so it persists even if the registering user is deleted.
// self_applied_by_tm: if true, company_id is set to TM's company record (officer/admin only).
// payment_to_pbt controls whether Sections 2 & 3 (BOQ/INV) are shown or hidden.
// application_status: cancelled locks all sections except Section 1.
// status: changes to 'completed' only when CPC is uploaded in Section 13.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->nullable();         // PBT Ref No
            $table->string('lor_no')->nullable();
            $table->string('project_no')->nullable();
            $table->text('project_desc');
            $table->string('pic_name');                   // Auto-filled from creator's name on creation
            $table->string('nd_state'); // Validated at Form Request level — no DB enum needed
            $table->foreignId('node_id')->nullable()->constrained('nodes')->nullOnDelete(); // TM Node
            $table->boolean('self_applied_by_tm')->default(false); // Officer/admin only — sets company to TM
            $table->enum('payment_to_pbt', ['charged', 'waived', 'not_required'])->nullable();
            $table->enum('application_status', ['in_progress', 'cancelled'])->default('in_progress');
            $table->text('cancellation_reason')->nullable(); // Required when application_status = cancelled
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

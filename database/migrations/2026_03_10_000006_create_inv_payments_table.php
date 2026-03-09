<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the inv_payments table (part of Step 3 — officer section).
// Up to 3 invoice payments (INV1, INV2, INV3) can be recorded per project.
// Each payment tracks the EDS number, date, amount, and payment status.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('inv_number', ['INV1', 'INV2', 'INV3']);
            $table->string('eds_no');
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_status', ['paid', 'outstanding']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_payments');
    }
};

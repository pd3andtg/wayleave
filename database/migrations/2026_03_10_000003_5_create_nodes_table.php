<?php

// Nodes table — stores TM node records (e.g. KT, KBR, TRG).
// Managed by Admin via UI — no code changes needed to add new nodes.
// Referenced by projects.node_id (nullable FK).
// Must run BEFORE create_projects_table since projects has node_id FK.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('acronym');       // Short name shown in search (e.g. KT, KBR)
            $table->string('full_name');     // Full node name for display
            $table->string('nd')->nullable();    // Network district (e.g. ND_TRG, ND_PHG)
            $table->string('state')->nullable(); // Malaysian state (e.g. Terengganu, Pahang)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};

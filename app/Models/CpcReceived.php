<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 12: contractor uploads the received CPC document and records the date.
// Creating this record triggers project status → completed.
// One record per project.
class CpcReceived extends Model
{
    // Table name is 'cpc_received', not the default 'cpc_receiveds'.
    protected $table = 'cpc_received';

    protected $fillable = [
        'project_id',
        'cpc_file',
        'cpc_date',
        'uploaded_by',
    ];

    protected $casts = [
        'cpc_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

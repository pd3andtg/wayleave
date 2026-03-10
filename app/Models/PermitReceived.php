<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 7: contractor records the permit received date and uploads the permit file.
// One record per project.
class PermitReceived extends Model
{
    // Table name is 'permits_received', not the default 'permit_receiveds'.
    protected $table = 'permits_received';

    protected $fillable = [
        'project_id',
        'permit_received_date',
        'permit_file',
        'uploaded_by',
    ];

    protected $casts = [
        'permit_received_date' => 'date',
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

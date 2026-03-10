<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 6: contractor submits the permit document to KUTT/PBT.
// One record per project.
class PermitSubmission extends Model
{
    protected $fillable = [
        'project_id',
        'submit_date',
        'submission_file',
        'submitted_by',
    ];

    protected $casts = [
        'submit_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}

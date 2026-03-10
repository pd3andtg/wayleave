<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 2 (contractor upload) and Step 3 (officer endorsement) of the workflow.
// One BQ/INV record per project.
class BqInv extends Model
{
    protected $fillable = [
        'project_id',
        'bq_inv_file',
        'endorsed_file',
        'payment_status',
        'uploaded_by',
        'endorsed_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function endorsedBy()
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }
}

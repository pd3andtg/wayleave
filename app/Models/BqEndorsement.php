<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 5 (officer section): endorsement record for a BQ-type file from bq_inv_files.
// One endorsement per BQ file. Officer fills document_info, date, and optional remarks.
class BqEndorsement extends Model
{
    protected $fillable = [
        'bq_inv_file_id',
        'project_id',
        'document_info',
        'date',
        'remarks',
        'endorsed_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function bqInvFile()
    {
        return $this->belongsTo(BqInvFile::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function endorsedBy()
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }
}

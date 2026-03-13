<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 5 (officer section): endorsement record for an INV-type file from bq_inv_files.
// Mirrors BqEndorsement but adds amount, eds_no, and payment_status.
// payment_status: paid / outstanding / waived (3 options).
class InvEndorsement extends Model
{
    protected $fillable = [
        'bq_inv_file_id',
        'project_id',
        'document_info',
        'date',
        'amount',
        'payment_status',
        'eds_no',
        'remarks',
        'endorsed_by',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
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

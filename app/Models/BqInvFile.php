<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 4: contractor uploads up to 6 BQ/INV files per project.
// Each file has its own metadata: document_info, payment_type, date, amount, eds_no, remarks.
// file_number (1–6) identifies which slot the file occupies.
// payment_type determines which endorsement table the officer uses in Step 5:
//   BQ → bq_endorsements, INV → inv_endorsements.
class BqInvFile extends Model
{
    protected $fillable = [
        'project_id',
        'file_number',
        'file_path',
        'document_info',
        'payment_type',
        'date',
        'amount',
        'eds_no',
        'remarks',
        'uploaded_by',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function bqEndorsement()
    {
        return $this->hasOne(BqEndorsement::class);
    }

    public function invEndorsement()
    {
        return $this->hasOne(InvEndorsement::class);
    }
}

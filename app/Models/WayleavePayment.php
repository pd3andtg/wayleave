<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 7 (officer section): FI and deposit payment details per PBT.
// Separated from wayleave_pbts so Step 6 (file upload + endorsement)
// and Step 7 (payment recording) have clear, distinct completion boundaries.
// fi_application_date and deposit_application_date track when payments were applied for.
class WayleavePayment extends Model
{
    protected $fillable = [
        'project_id',
        'wayleave_pbt_id',
        'fi_payment',
        'fi_eds_no',
        'fi_application_date',
        'deposit_payment',
        'deposit_eds_no',
        'deposit_payment_type',
        'deposit_application_date',
        'recorded_by',
    ];

    protected $casts = [
        'fi_application_date'      => 'date',
        'deposit_application_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function wayleavePhbt()
    {
        return $this->belongsTo(WayleavePhbt::class, 'wayleave_pbt_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

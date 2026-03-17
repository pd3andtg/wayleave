<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Shared model for Section 6 (TM: Wayleave Payment Details) and
// Section 7 (TM: BG & BD Received from FINSSO).
// One row per payment_type (FI or Deposit) per PBT.
// Section 7 shows only rows where status = required, plus received_posted_date + bg_bd_file_path.
// method_of_payment applies to BOTH FI and Deposit rows (BG, BD_DAP, EFT_DAP).
class WayleavePayment extends Model
{
    protected $fillable = [
        'project_id',
        'wayleave_pbt_id',
        'payment_type',
        'status',
        'amount',
        'eds_no',
        'method_of_payment',
        'application_date',
        'received_posted_date',
        'bg_bd_file_path',
        'recorded_by',
    ];

    protected $casts = [
        'application_date'     => 'date',
        'received_posted_date' => 'date',
        'amount'               => 'decimal:2',
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

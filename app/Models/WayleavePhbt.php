<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 6: contractor uploads the wayleave file received from KUTT/PBT.
// Officer then overwrites wayleave_file with the endorsed version and
// endorsement_remarks is automatically set to "Endorsed" on upload.
// Up to 3 PBT records per project (pbt_number: PBT1, PBT2, PBT3).
// pbt_name_other is required only when pbt_name is set to 'Others'.
// FI and deposit payment details live in wayleave_payments (Step 7).
class WayleavePhbt extends Model
{
    // Table name does not follow default Laravel pluralisation convention.
    protected $table = 'wayleave_pbts';

    protected $fillable = [
        'project_id',
        'pbt_number',
        'pbt_name',
        'pbt_name_other',
        'wayleave_file',
        'wayleave_received_date',
        'endorsement_remarks',
        'endorsed_by',
    ];

    protected $casts = [
        'wayleave_received_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function endorsedBy()
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }

    public function payment()
    {
        return $this->hasOne(WayleavePayment::class, 'wayleave_pbt_id');
    }
}

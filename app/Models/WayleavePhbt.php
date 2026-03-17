<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Section 4: Contractor uploads the wayleave file per PBT.
// Section 5: Officer overwrites wayleave_file with the endorsed version and sets endorsed_by.
//            No endorsement_remarks — Section 5 only has file upload + endorsed_by.
// endorsed_by is displayed in BOTH Section 4 and Section 5.
// Up to 3 PBT records per project (pbt_number: PBT1, PBT2, PBT3).
// pbt_name_other is required only when pbt_name = 'Others'.
// Payments live in wayleave_payments (Sections 6 & 7).
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
        'endorsed_by',
    ];

    protected $casts = [
        'wayleave_received_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // The officer who endorsed (overwrote) the wayleave file in Section 5
    public function endorsedBy()
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }

    // Payments for this PBT (FI row and Deposit row)
    public function payments()
    {
        return $this->hasMany(WayleavePayment::class, 'wayleave_pbt_id');
    }
}

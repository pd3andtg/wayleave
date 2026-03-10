<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Steps 4 (contractor upload) and 5 (officer endorsement + payment) of the workflow.
// Up to 3 PBT records per project (pbt_number: PBT1, PBT2, PBT3).
// pbt_name_other is required only when pbt_name is set to 'Others'.
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
        'endorsed_file',
        'fi_payment',
        'fi_eds_no',
        'fi_date',
        'deposit_payment',
        'deposit_eds_no',
        'deposit_payment_type',
        'deposit_date',
        'endorsed_by',
    ];

    protected $casts = [
        'wayleave_received_date' => 'date',
        'fi_date'                => 'date',
        'deposit_date'           => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function endorsedBy()
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }
}

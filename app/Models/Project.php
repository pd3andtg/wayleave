<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Central record for each wayleave project.
// All 12 workflow steps hang off this model.
// Contractors are always scoped by company_id — never trust user input.
class Project extends Model
{
    protected $fillable = [
        'ref_no',
        'lor_no',
        'project_no',
        'project_desc',
        'nd_state',
        'remarks',
        'company_id',
        'created_by',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Step 4: up to 6 BQ/INV files per project
    public function bqInvFiles()
    {
        return $this->hasMany(BqInvFile::class);
    }

    // Step 5: endorsements for BQ-type files
    public function bqEndorsements()
    {
        return $this->hasMany(BqEndorsement::class);
    }

    // Step 5: endorsements for INV-type files
    public function invEndorsements()
    {
        return $this->hasMany(InvEndorsement::class);
    }

    // Step 6: up to 3 PBTs per project
    public function wayleavePhbts()
    {
        return $this->hasMany(WayleavePhbt::class);
    }

    // Step 7: FI and deposit payment per PBT
    public function wayleavePayments()
    {
        return $this->hasMany(WayleavePayment::class);
    }

    public function permitSubmission()
    {
        return $this->hasOne(PermitSubmission::class);
    }

    public function permitReceived()
    {
        return $this->hasOne(PermitReceived::class);
    }

    public function workNotice()
    {
        return $this->hasOne(WorkNotice::class);
    }

    public function cpcApplication()
    {
        return $this->hasOne(CpcApplication::class);
    }

    public function cpcReceived()
    {
        return $this->hasOne(CpcReceived::class);
    }
}

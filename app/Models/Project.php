<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Central record for each wayleave project.
// All 10 workflow steps hang off this model.
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

    public function bqInv()
    {
        return $this->hasOne(BqInv::class);
    }

    public function invPayments()
    {
        return $this->hasMany(InvPayment::class);
    }

    public function wayleavePhbts()
    {
        return $this->hasMany(WayleavePhbt::class);
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Central record for each wayleave project.
// All 13 workflow sections hang off this model.
// Contractors are always scoped by company_id — never trust user input.
//
// payment_to_pbt = waived/not_required -> Sections 2 & 3 are hidden (data preserved).
// application_status = cancelled -> all sections locked except Section 1.
// self_applied_by_tm = true -> company_id is set to TM's company record.
class Project extends Model
{
    protected $fillable = [
        'ref_no',
        'lor_no',
        'project_no',
        'project_desc',
        'pic_name',
        'nd_state',
        'node_id',
        'self_applied_by_tm',
        'payment_to_pbt',
        'application_status',
        'cancellation_reason',
        'remarks',
        'company_id',
        'created_by',
        'status',
    ];

    protected $casts = [
        'self_applied_by_tm' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    // Sections 2 & 3: shared BOQ/INV items table
    public function boqInvItems()
    {
        return $this->hasMany(BoqInvItem::class);
    }

    // Section 4 & 5: up to 3 PBTs per project
    public function wayleavePhbts()
    {
        return $this->hasMany(WayleavePhbt::class);
    }

    // Sections 6 & 7: FI and deposit payment per PBT
    public function wayleavePayments()
    {
        return $this->hasMany(WayleavePayment::class);
    }

    // Section 8 — up to 3 submissions per project
    public function permitSubmissions()
    {
        return $this->hasMany(PermitSubmission::class);
    }

    // Section 9 — up to 3 permits received per project
    public function permitReceiveds()
    {
        return $this->hasMany(PermitReceived::class);
    }

    // Sections 10 & 11 (notis_mula_file and notis_siap_file — same table, two sections)
    public function workNotice()
    {
        return $this->hasOne(WorkNotice::class);
    }

    // Section 12
    public function cpcApplication()
    {
        return $this->hasOne(CpcApplication::class);
    }

    // Section 13
    public function cpcReceived()
    {
        return $this->hasOne(CpcReceived::class);
    }

    // Helper: is this project cancelled?
    public function isCancelled(): bool
    {
        return $this->application_status === 'cancelled';
    }

    // Helper: should Sections 2 & 3 be hidden due to payment_to_pbt setting?
    public function isBoqHidden(): bool
    {
        return in_array($this->payment_to_pbt, ['waived', 'not_required']);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Shared model for Section 2 (BOQ/INV Files) and Section 3 (TM BOQ/Invoice Endorsement).
// Section 2 displays a subset of columns; Section 3 displays all columns.
// file_path is shared — officer upload overwrites contractor's original.
// endorsed_by is set when officer uploads the endorsed file in Section 3.
class BoqInvItem extends Model
{
    protected $fillable = [
        'project_id',
        'document_info',
        'type',
        'date_received',
        'amount',
        'file_path',
        'eds_no',
        'eds_application_date',
        'payment_status',
        'endorsed_by',
        'remarks',
        'updated_by',
    ];

    protected $casts = [
        'date_received'       => 'date',
        'amount'              => 'decimal:2',
        'eds_application_date'=> 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // The officer who uploaded the endorsed file
    public function endorsedBy()
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }

    // The user who last updated this row
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

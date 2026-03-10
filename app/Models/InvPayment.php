<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Invoice payment records for a project (up to 3: INV1, INV2, INV3).
// Managed by officers as part of Step 3.
class InvPayment extends Model
{
    protected $fillable = [
        'project_id',
        'inv_number',
        'eds_no',
        'date',
        'amount',
        'payment_status',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

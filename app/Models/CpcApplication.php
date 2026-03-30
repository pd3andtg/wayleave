<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 9: contractor uploads all CPC application documents and submits to PBT.
// One record per project.
class CpcApplication extends Model
{
    protected $fillable = [
        'project_id',
        'surat_serahan_file',
        'laporan_bergambar_file',
        'salinan_coa_file',
        'salinan_permit_file',
        'date_submit_to_pbt',
        'submitted_by',
    ];

    protected $casts = [
        'date_submit_to_pbt' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}

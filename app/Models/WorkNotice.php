<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 8: contractor uploads Notis Mula, Notis Siap, and a combined site photos PDF.
// One record per project. Gambar file must be a single combined PDF.
class WorkNotice extends Model
{
    protected $fillable = [
        'project_id',
        'notis_mula_file',
        'notis_siap_file',
        'gambar_file',
        'uploaded_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

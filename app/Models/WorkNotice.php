<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Step 10: contractor uploads Notis Mula Kerja and Notis Siap Kerja.
// Gambar (site photos) has been removed entirely from the system.
// One record per project.
class WorkNotice extends Model
{
    protected $fillable = [
        'project_id',
        'notis_mula_file',
        'notis_siap_file',
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

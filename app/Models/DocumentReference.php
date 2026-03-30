<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Reference documents uploaded by Admin.
// All authenticated users (admin, officer, contractor) can view and download.
// Only admin can upload, edit, or delete.
class DocumentReference extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_path',
        'original_filename',
        'uploaded_by',
    ];

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

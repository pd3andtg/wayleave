<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Represents a contractor company.
// Companies must be approved by Admin before contractors can register under them.
class Company extends Model
{
    protected $fillable = ['name', 'status', 'requested_by', 'approved_by'];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Represents a TM Tech internal division (e.g. ND TRG, ND KEL, ND PHG).
// Stored as a separate table so Admin can add new units via the UI without code changes.
class Unit extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

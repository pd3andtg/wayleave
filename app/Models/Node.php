<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Stores TM node records — Admin manages via UI.
// Referenced by projects.node_id.
// Searchable by acronym or full_name in project forms.
class Node extends Model
{
    protected $fillable = ['acronym', 'full_name'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}

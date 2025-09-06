<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPhase extends Model
{
    protected $fillable = [
        'phases_name',
        'status',
        'start_date',
        'end_date',
        'remarks'
    ];

    // One-to-many relationship with tasks
    public function tasks()
    {
        return $this->hasMany(Task::class, 'phase_id');
    }

    // Many-to-many relationship with projects via pivot table
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_project_phase')
                    ->withTimestamps();
    }
}

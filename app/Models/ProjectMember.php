<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectMember extends Pivot
{
    protected $table = 'project_members';
    protected $fillable = ['project_id','user_id','assigned_role_id'];

    // Relations
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class,'assigned_role_id');
    }
}

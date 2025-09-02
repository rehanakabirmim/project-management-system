<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'role_name',
        'guard_name',
        'level',
        'parent_id',
        'can_manage_projects'
    ];

    // Parent role
    public function parent() {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    // Child roles
    public function children() {
        return $this->hasMany(Role::class, 'parent_id');
    }
}


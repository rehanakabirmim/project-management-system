<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;


use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectMember extends Pivot
{
    protected $table = 'project_members';
    protected $fillable = ['project_id','user_id','assigned_role_id'];
}


// class ProjectMember extends Model
// {
    
// }

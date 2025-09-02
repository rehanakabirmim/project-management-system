<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeadlineExtension extends Model
{
    protected $fillable = ['project_id','task_id','old_deadline','new_deadline','reason','requested_by','approved_by','status'];

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function task(){
        return $this->belongsTo(Task::class);
    }

    public function requester(){
        return $this->belongsTo(User::class,'requested_by');
    }

    public function approver(){
        return $this->belongsTo(User::class,'approved_by');
    }
}

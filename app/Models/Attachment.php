<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['project_id','task_id','uploaded_by','file_path','file_type'];

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function task(){
        return $this->belongsTo(Task::class);
    }

    public function uploader(){
        return $this->belongsTo(User::class,'uploaded_by');
    }
}


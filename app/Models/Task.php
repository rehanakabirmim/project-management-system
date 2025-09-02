<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id','phase_id','task_name','description','assigned_to','start_date','end_date',
        'progress_percent','total_progress_percent','status','running_state','approximate_delivery_date',
        'is_completed','is_delivered','post_delivery_state','price','source_code','live_demo','client_mood','remarks'
    ];

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function phase(){
        return $this->belongsTo(ProjectPhase::class,'phase_id');
    }

    public function assignee(){
        return $this->belongsTo(User::class,'assigned_to');
    }
}


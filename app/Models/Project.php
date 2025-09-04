<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'project_name','client_id','profile_name','status','start_date','end_date','deadline','remaining_hours',
        'order_sheet_link','total_amount','running_state','delivery_date','is_delivered','post_delivery_state',
        'client_mood','issue','color_code','created_by'
    ];

    public function client(){
        return $this->belongsTo(Client::class);
    }

    public function phases(){
        return $this->hasMany(ProjectPhase::class);
    }

    public function tasks(){
        return $this->hasMany(Task::class);
    }

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }


   public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('assigned_role_id')
                    ->withTimestamps();
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


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


 

    
   // Deadline in full days (integer)
    public function getDeadlineDaysAttribute()
        {
            if (!$this->deadline) return null;

            $now = Carbon::now();
            $deadline = Carbon::parse($this->deadline);

            // diffInDays(false) returns signed integer days, no fraction
            $days = $now->diffInDays($deadline, false); 

            return max((int)$days, 0) . ' days'; // cast to integer explicitly
        }

// Remaining time in hours and minutes
    public function getRemainingTimeAttribute()
    {
        if (!$this->deadline) return null;

        $now = Carbon::now();
        $deadline = Carbon::parse($this->deadline);

        if ($now->gte($deadline)) {
            return '0 hours 0 minutes';
        }

        // difference in total minutes
        $diffInMinutes = $now->diffInMinutes($deadline);

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        return "{$hours} hours {$minutes} minutes";
    }

    // Color code based on remaining days
    public function getColorCodeAttribute()
    {
        $now = Carbon::now();
        $deadline = Carbon::parse($this->deadline);

        if ($this->status === 'completed') return 'blue';
        if ($this->status === 'extended') return 'lightgreen';
        if ($now->gt($deadline)) return 'red';
        if ($now->diffInDays($deadline) <= 5) return 'yellow';

        return 'green';
    }

}

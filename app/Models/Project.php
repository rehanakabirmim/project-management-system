<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Project extends Model
{
    protected $fillable = [
        'project_name',
        'client_id',
        'profile_name',
        'status',
        'start_date',
        'end_date',
        'deadline',          // optional, final deadline date
        'remaining_hours',   // optional
        'order_sheet_link',  // external file URL
        'total_amount',
        'running_state',
        'delivery_date',
        'is_delivered',
        'post_delivery_state',
        'client_mood',
        'issue',
        'created_by',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function phases()
    {
        return $this->hasMany(ProjectPhase::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('assigned_role_id')
                    ->withTimestamps();
    }

    
   // Accessor: deadline_days (full integer days remaining)
     public function getDeadlineDaysAttribute()
    {
        $now = Carbon::now();

        // Use 'deadline' if exists, otherwise fallback to 'end_date'
        $deadline = $this->deadline ? Carbon::parse($this->deadline) 
                    : ($this->end_date ? Carbon::parse($this->end_date) : null);
        if (!$deadline) return null;

        $days = $now->diffInDays($deadline, false); // integer
        return max((int)$days, 0) . ' days';        // append 'days'
    }

    // Accessor: remaining_time in "X hours Y minutes"
    public function getRemainingTimeAttribute()
    {
        $now = Carbon::now();

        $deadline = $this->deadline ? Carbon::parse($this->deadline) : ($this->end_date ? Carbon::parse($this->end_date) : null);
        if (!$deadline) return null;

        if ($now->gte($deadline)) {
            return '0 hours 0 minutes';
        }

        $diffInMinutes = $now->diffInMinutes($deadline);
        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        return "{$hours} hours {$minutes} minutes";
    }

    // Accessor: color_code based on remaining days or status
    public function getColorCodeAttribute()
    {
        $now = Carbon::now();

        $deadline = $this->deadline ? Carbon::parse($this->deadline) : ($this->end_date ? Carbon::parse($this->end_date) : null);
        if (!$deadline) return 'gray';

        $remainingDays = $now->diffInDays($deadline, false);

        if ($this->status === 'completed') return 'blue';
        if ($remainingDays < 0) return 'red';       // past due
        if ($remainingDays <= 5) return 'yellow';   // near deadline
        return 'green';                             // safe
    }
}

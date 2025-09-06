<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'parent_id',
        'comment'
    ];

    // Comment লেখার user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Project relation
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Task relation
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Parent comment (যদি reply হয়)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Replies (recursive)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->with('user', 'replies'); // recursive with user
    }
}

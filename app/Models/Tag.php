<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['tag_name','color_code'];

    public function projects(){
        return $this->morphedByMany(Project::class,'taggable');
    }

    public function tasks(){
        return $this->morphedByMany(Task::class,'taggable');
    }
}

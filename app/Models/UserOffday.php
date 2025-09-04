<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOffday extends Model
{
    // Fillable fields
    protected $fillable = [
        'user_id',
        'off_day',   
        'reason'
    ];

    // Relationship: each offday belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

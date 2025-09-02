<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['client_name','profile_name','mood'];

    public function projects(){
        return $this->hasMany(Project::class);
    }
}

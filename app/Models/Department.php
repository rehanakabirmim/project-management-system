<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['dept_name','description'];

    public function users(){
        return $this->hasMany(User::class);
    }
}

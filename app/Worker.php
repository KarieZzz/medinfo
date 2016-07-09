<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    //
    protected $fillable = ['name', 'password', 'email', 'description', 'role', 'permission', 'blocked'];
    protected $hidden = ['password'];

    public function scopeOfRole($query, $type)
    {
        return $query->where('role', $type);
    }

    public function scopeExperts($query)
    {
        return $query->where('role', 2);
    }

}

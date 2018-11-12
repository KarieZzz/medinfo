<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    //
    protected $fillable = ['user_id', 'name', 'value'];

    public function scopeOfUser($query, $user)
    {
        return $query->where('user_id', $user);
    }
}

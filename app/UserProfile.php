<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    //
    protected $fillable = ['user_id', 'tag', 'attribute', 'value'];

    public function scopeOfUser($query, $user)
    {
        return $query->where('user_id', $user);
    }
}

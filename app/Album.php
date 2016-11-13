<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    //
    protected $fillable = ['album_name', 'default'];

    public function scopeDefault($query)
    {
        return $query
            ->where('default', true);
    }
}

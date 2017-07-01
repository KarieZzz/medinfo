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

    public function scopeGeneral($query)
    {
        return $query
            ->where('id', config('medinfo.general_album'));
    }

}

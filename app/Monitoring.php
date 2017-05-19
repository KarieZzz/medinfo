<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    //
    protected $fillable = ['name', 'periodicity', 'album_id'];

    public function album()
    {
        return $this->belongsTo('App\Album');
    }
}

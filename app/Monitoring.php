<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    //
    protected $fillable = ['name', 'periodicity', 'accumulation', 'album_id'];

    public function album()
    {
        return $this->belongsTo('App\Album');
    }
}

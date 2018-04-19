<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PeriodPattern extends Model
{
    //
    protected $fillable = ['name', 'periodicity', 'begin', 'end'];

    public function periodicity()
    {
        return $this->belongsTo('App\DicPeriodicity', 'periodicity', 'code');
    }
}

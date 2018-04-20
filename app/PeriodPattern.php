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

    public function scopeYear($query)
    {
        return $query
            ->where('periodicity', 1)
            ->where('begin', '01-01')
            ->where('end', '12-31');
    }

    public function scopeI($query)
    {
        return $query
            ->where('periodicity', 3)
            ->where('begin', '01-01')
            ->where('end', '03-31');
    }

    public function scopeII($query)
    {
        return $query
            ->where('periodicity', 3)
            ->where('begin', '04-01')
            ->where('end', '06-30');
    }

    public function scopeIII($query)
    {
        return $query
            ->where('periodicity', 3)
            ->where('begin', '07-01')
            ->where('end', '09-30');
    }

    public function scopeIV($query)
    {
        return $query
            ->where('periodicity', 3)
            ->where('begin', '10-01')
            ->where('end', '12-31');
    }

    public function scopeIplus($query)
    {
        return $query
            ->where('periodicity', 4)
            ->where('begin', '01-01')
            ->where('end', '03-31');
    }

    public function scopeIIplus($query)
    {
        return $query
            ->where('periodicity', 4)
            ->where('begin', '01-01')
            ->where('end', '06-30');
    }

    public function scopeIIIplus($query)
    {
        return $query
            ->where('periodicity', 4)
            ->where('begin', '01-01')
            ->where('end', '09-30');
    }

    public function scopeIVplus($query)
    {
        return $query
            ->where('periodicity', 4)
            ->where('begin', '01-01')
            ->where('end', '12-31');
    }
}

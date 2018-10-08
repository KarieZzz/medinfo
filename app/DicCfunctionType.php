<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DicCfunctionType extends Model
{
    //
    protected $primaryKey = 'code';

    public function scopeInForm($query)
    {
        return $query
            ->where('name', 'Внутриформенный');
    }

    public function scopeInterForm($query)
    {
        return $query
            ->where('name', 'Межформенный');
    }

    public function scopeInterPeriod($query)
    {
        return $query
            ->where('name', 'Межпериодный');
    }
}

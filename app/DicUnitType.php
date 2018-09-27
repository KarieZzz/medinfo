<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DicUnitType extends Model
{
    //
    public function scopeOuTypes($query)
    {
        return $query->where('code', '<>', '100');
    }
}

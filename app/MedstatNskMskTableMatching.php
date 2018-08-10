<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskMskTableMatching extends Model
{
    //
    protected $fillable = ['mds', 'msk'];

    public function scopeOfMds($query, $formtable)
    {
        return $query
            ->where('mds', $formtable);
    }
}

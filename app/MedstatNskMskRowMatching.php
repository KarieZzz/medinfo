<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskMskRowMatching extends Model
{
    //
    protected $fillable = ['mdstable', 'mdsrow', 'mskrow'];


    public function scopeOfMds($query, $formtable, $row)
    {
        return $query
            ->where('mdstable', $formtable)
            ->where('mdsrow', $row);
    }
}

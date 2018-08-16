<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskMskColumnMatching extends Model
{
    //
    protected $fillable = ['mdstable', 'mdscol', 'mskcol', 'transposed'];

    public function scopeOfMds($query, $formtable, $column)
    {
        return $query
            ->where('mdstable', $formtable)
            ->where('mdscol', $column);
    }
}

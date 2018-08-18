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

    public function scopeFT($query, $formtable)
    {
        return $query
            ->where('mdstable', $formtable);
    }
}

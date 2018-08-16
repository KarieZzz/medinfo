<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskColumnLink extends Model
{
    //
    protected $fillable = ['table', 'column' , 'medstat_code', 'transposed'];

    public function scopeOfTable($query, $id)
    {
        return $query
            ->where('table', $id);
    }

    public function scopeTransposed($query)
    {
        return $query->where('transposed', true);
    }

}

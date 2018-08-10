<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskColumnLink extends Model
{
    //
    protected $fillable = ['table', 'column' , 'medstat_code'];

    public function scopeOfTable($query, $id)
    {
        return $query
            ->where('table', $id);
    }
}

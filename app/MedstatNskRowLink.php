<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskRowLink extends Model
{
    //
    protected $fillable = ['table', 'row' , 'medstat_code'];

    public function scopeOfTable($query, $id)
    {
        return $query
            ->where('table', $id);
    }
}

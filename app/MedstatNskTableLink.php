<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskTableLink extends Model
{
    //
    protected $fillable = ['form_id', 'tablen', 'name', 'colcount', 'rowcount', 'fixcols', 'fixrows', 'floattype', 'scan', 'medstat_code'];

    public function scopeOfForm($query, $id)
    {
        return $query
            ->where('form_id', $id);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskTableLink extends Model
{
    //
    protected $fillable = ['form_id', 'tablen', 'name', 'colcount', 'rowcount', 'fixcols', 'fixrows', 'floattype', 'scan', 'medstat_code', 'transposed'];

    public function table()
    {
        return $this->hasOne('App\Table', 'medstatnsk_id', 'id' );
    }

    public function formnsk()
    {
        return $this->belongsTo('App\MedstatNskFormLink', 'form_id', 'id');
    }

    public function scopeOfForm($query, $id)
    {
        return $query
            ->where('form_id', $id);
    }
}

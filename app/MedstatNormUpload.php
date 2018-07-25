<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNormUpload extends Model
{
    //
    protected $guarded = ['id'];
    public $timestamps = false;

    public function medinfoform()
    {
        return $this->belongsTo('App\Form', 'form', 'medstat_code');
    }
}

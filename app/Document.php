<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    //
    protected $fillable = [
        'ou_id' , 'period_id', 'form_id', 'state',
    ];
}

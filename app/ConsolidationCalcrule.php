<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsolidationCalcrule extends Model
{
    //
    protected $fillable = ['script', 'hash', 'comment'];
    protected $hidden = ['ptree', 'properties', ];

}

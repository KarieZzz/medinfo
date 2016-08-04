<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    //
    protected $fillable = ['name', 'begin_date', 'end_date', 'pattern_id', 'medinfo_id'];

}

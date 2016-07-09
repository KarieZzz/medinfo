<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    //
    protected $fillable = ['row_index', 'row_code', 'row_name', 'medstat_code', 'medinfo_id' ];
}

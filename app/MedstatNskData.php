<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskData extends Model
{
    //
    protected $table = 'medstat_nsk_data';
    protected $fillable = ['hospital', 'data', 'year', 'table', 'column', 'row'];

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MsStr extends Model
{
    //
    protected $table = 'ms_str';
    protected $fillable = ['rec_id', 'a1', 'a2', 'gt', 'syncronized_at'];
}

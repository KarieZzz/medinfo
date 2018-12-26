<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MsGrf extends Model
{
    //
    protected $table = 'ms_grf';
    protected $fillable = ['rec_id', 'a1', 'a2', 'gt', 'a3', 'syncronized_at'];
}

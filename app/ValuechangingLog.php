<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ValuechangingLog extends Model
{
    //
    protected $table = 'valuechanging_log';
    protected $fillable = [ 'worker_id', 'oldvalue', 'newvalue', 'd', 'o', 'f', 't', 'r', 'c', 'p', 'occured_at' ];
    public $timestamps = false;
    protected $dates = ['occured_at'];
}

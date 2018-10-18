<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{

    protected $table = 'access_log';
    protected $fillable = ['user_id', 'occured_at'];
    protected $dates = ['occured_at', ];
    public $timestamps = false;
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkerSetting extends Model
{
    //
    protected $fillable = ['worker_id', 'name', 'value'];
}

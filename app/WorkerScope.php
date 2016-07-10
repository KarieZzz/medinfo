<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkerScope extends Model
{
    //
    public function workers()
    {
        return $this->belongsTo('App\Worker', 'id' , 'worker_id');
    }
}

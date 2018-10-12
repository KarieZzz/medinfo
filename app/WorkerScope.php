<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkerScope extends Model
{
    //
    protected $fillable = ['worker_id', 'ou_id', 'with_descendants'];

    public function workers()
    {
        return $this->belongsTo('App\Worker', 'id' , 'worker_id');
    }

    public function unit()
    {
        return $this->belongsTo('App\Unit', 'id' , 'ou_id');
    }

    public function scopeWorker($query, $worker)
    {
        return $query->where('worker_id', $worker);
    }
}
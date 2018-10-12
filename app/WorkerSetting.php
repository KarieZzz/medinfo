<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkerSetting extends Model
{
    //
    protected $fillable = ['worker_id', 'name', 'value'];

    public function scopeOfWorker($query, $worker)
    {
        return $query->where('worker_id', $worker);
    }
}

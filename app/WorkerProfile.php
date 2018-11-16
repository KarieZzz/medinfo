<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkerProfile extends Model
{
    //
    protected $fillable = ['worker_id', 'tag', 'attribute', 'value'];

    public function worker()
    {
        return $this->belongsTo('App\Worker');
    }

    public function scopeOfWorker($query, $worker)
    {
        return $query->where('worker_id', $worker);
    }
}

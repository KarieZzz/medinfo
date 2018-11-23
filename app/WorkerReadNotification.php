<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkerReadNotification extends Model
{
    //
    protected $fillable = ['worker_id', 'event_uid', 'event_type', 'occured_at'];
    public $timestamps = false;
    protected $dates = ['occured_at'];

    public function worker()
    {
        return $this->belongsTo('App\Worker');
    }

    public function message()
    {
        return $this->belongsTo('App\DocumentMessage', 'event_uid', 'uid');
    }

    public function scopeOfWorker($query, $worker)
    {
        return $query->where('worker_id', $worker);
    }
}

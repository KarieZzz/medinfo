<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecentDocument extends Model
{
    //
    protected $fillable = ['worker_id', 'document_id', 'occured_at'];
    public $timestamps = false;
    protected $dates = ['occured_at'];

    public function worker()
    {
        return $this->belongsTo('App\Worker');
    }

    public function document()
    {
        return $this->belongsTo('App\Document');
    }

    public function scopeOfWorker($query, $worker)
    {
        return $query->where('worker_id', $worker);
    }

}

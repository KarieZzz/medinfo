<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatechangingLog extends Model
{
    //
    protected $table = 'statechanging_log';
    protected $fillable = [ 'worker_id', 'document_id', 'oldstate', 'newstate', 'occured_at' ];
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

    public function oldstate()
    {
        return $this->belongsTo('App\DicDocumentState', 'oldstate' , 'code');
    }

    public function newstate()
    {
        return $this->belongsTo('App\DicDocumentState', 'newstate' , 'code');
    }

    public function scopeOfDocument($query, $document)
    {
        return $query
            ->where('document_id', $document);
    }

    public function scopeOfWorker($query, $worker)
    {
        return $query->where('worker_id', $worker);
    }
}

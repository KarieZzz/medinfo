<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DocumentMessage extends Model
{
    //
    protected $fillable = [ 'doc_id', 'user_id', 'message', 'uid', ];
    //protected $attributes = ['DateDiff' => '', ];

    protected $appends = ['CreatedTS'];

    public function getCreatedTSAttribute()
    {
        return $this->created_at->timestamp;
    }

    public function scopeOfWorker($query, $worker)
    {
        return $query->where('user_id', $worker);
    }

    public function scopeOfDocument($query, $document)
    {
        return $query->where('doc_id', $document);
    }

    public function worker()
    {
        return $this->belongsTo('App\Worker', 'user_id', 'id');
    }

    public function document()
    {
        return $this->belongsTo('App\Document', 'doc_id', 'id');
    }

    public function is_read()
    {
        return $this->hasMany('App\WorkerReadNotification', 'event_uid', 'uid');
    }
}

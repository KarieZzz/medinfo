<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SectionchangingLog extends Model
{
    //
    protected $table = 'sectionchanging_log';
    protected $fillable = [ 'worker_id', 'document_id', 'blocked', 'occured_at' ];
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

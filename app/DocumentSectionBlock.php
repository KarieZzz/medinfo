<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentSectionBlock extends Model
{
    //
    protected $fillable = ['formsection_id', 'document_id', 'worker_id', 'blocked'];

    public function formsection()
    {
        return $this->belongsTo('App\FormSection');
    }

    public function document()
    {
        return $this->belongsTo('App\Document');
    }

    public function worker()
    {
        return $this->belongsTo('App\Worker');
    }

    public function scopeOfFormSection($query, $section)
    {
        return $query
            ->where('formsection_id', $section);
    }

    public function scopeOfDocument($query, $document)
    {
        return $query
            ->where('document_id', $document);
    }

    public function scopeSD($query, $section, $document)
    {
        return $query
            ->where('formsection_id', $section)
            ->where('document_id', $document);
    }

    public function scopeOfWorker($query, $worker)
    {
        return $query
            ->where('worker_id', $worker);
    }

    public function scopeBlocked($query)
    {
        return $query
            ->where('blocked', true);
    }

}

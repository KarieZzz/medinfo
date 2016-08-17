<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlCashe extends Model
{
    //
    protected $table = 'control_cashe';
    protected $fillable = ['doc_id', 'table_id', 'cashed_at', 'control_cashe'];
    protected $dates = ['cashed_at'];
    public $timestamps = false;

    public function document()
    {
        return $this->belongsTo('App\Document', 'doc_id');
    }

    public function table()
    {
        return $this->belongsTo('App\Table');
    }

    public function scopeOfDocumentTable($query, $document, $table)
    {
        return $query
            ->where('doc_id', $document)
            ->where('table_id', $table);
    }
}

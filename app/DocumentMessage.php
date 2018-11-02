<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentMessage extends Model
{
    //
    protected $fillable = [
        'doc_id', 'user_id', 'message',
    ];

    public function scopeOfWorker($query, $worker)
    {
        return $query->where('user_id', $worker);
    }

    public function worker()
    {
        return $this->belongsTo('App\Worker', 'user_id', 'id');
    }
}

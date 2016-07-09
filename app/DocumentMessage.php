<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentMessage extends Model
{
    //
    protected $fillable = [
        'doc_id', 'user_id', 'message',
    ];

    public function worker()
    {
        return $this->hasOne('App\Worker', 'id', 'user_id');
    }
}

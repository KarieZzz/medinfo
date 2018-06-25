<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ValuechangingLog extends Model
{
    //
    protected $table = 'valuechanging_log';
    protected $fillable = [ 'worker_id', 'oldvalue', 'newvalue', 'd', 'o', 'f', 't', 'r', 'c', 'p', 'occured_at' ];
    public $timestamps = false;
    protected $dates = ['occured_at'];

    public function scopeOfDocument($query, $document)
    {
        return $query
            ->where('d', $document);
    }

    public function worker()
    {
        return $this->belongsTo('App\Worker');
    }

    public function form()
    {
        return $this->belongsTo('App\Form', 'f');
    }

    public function table()
    {
        return $this->belongsTo('App\Table', 't');
    }

    public function row()
    {
        return $this->belongsTo('App\Row', 'r');
    }

    public function column()
    {
        return $this->belongsTo('App\Column', 'c');
    }
}

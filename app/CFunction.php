<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CFunction extends Model
{
    //
    protected $table = 'cfunctions';
    protected $fillable = ['table_id', 'level', 'script', 'comment', 'blocked', 'compiled' ];

    public function scopeOfTable($query, $table)
    {
        return $query->where('table_id', $table);
    }

    public function table()
    {
        return $this->belongsTo('App\Table');
    }
}

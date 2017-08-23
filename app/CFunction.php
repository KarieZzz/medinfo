<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CFunction extends Model
{
    //
    protected $table = 'cfunctions';
    protected $fillable = ['table_id', 'level', 'type', 'script', 'comment', 'blocked', ];
    protected $hidden = ['compiled_cashe'];

    public function scopeOfTable($query, $table)
    {
        return $query->where('table_id', $table);
    }

    public function scopeOfLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('level', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('blocked', 0);
    }

    public function table()
    {
        return $this->belongsTo('App\Table');
    }

    public function level()
    {
        return $this->belongsTo('App\DicErrorLevel', 'level', 'code');
    }
}

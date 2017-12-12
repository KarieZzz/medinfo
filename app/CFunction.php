<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CFunction extends Model
{
    //
    protected $table = 'cfunctions';
    protected $fillable = ['table_id', 'level', 'type', 'function', 'script', 'comment', 'blocked', ];
    protected $hidden = ['ptree', 'properties', ];

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
        return $query->where('type', $type);
    }

    public function scopeInForm($query)
    {
        return $query->where('type', 1);
    }

    public function scopeInterForm($query)
    {
        return $query->where('type', 2);
    }

    public function scopeInterPeriod($query)
    {
        return $query->where('type', 3);
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

    public function type()
    {
        return $this->belongsTo('App\DicCfunctionType', 'type', 'code');
    }
}

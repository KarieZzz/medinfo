<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ColumnCalculation extends Model
{
    //
    protected $fillable = ['column_id', 'formula', 'comment'];

    public function column()
    {
        return $this->belongsTo('App\Column');
    }

    public function scopeOfColumn($query, $column_id)
    {
        return $query->where('column_id', $column_id);
    }
}

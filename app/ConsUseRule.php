<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsUseRule extends Model
{
    //
    protected $fillable = ['row_id', 'col_id', 'script'];

    public function rulescript()
    {
        return $this->belongsTo('App\ConsolidationRule', 'script');
    }

    public function scopeOfRC($query, $row, $column)
    {
        return $query
            ->where('row_id', $row)
            ->where('col_id', $column);
    }
}

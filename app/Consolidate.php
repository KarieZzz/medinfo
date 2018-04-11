<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consolidate extends Model
{
    //
    protected $fillable = ['doc_id', 'row_id', 'column_id', 'protocol', 'consolidated_at' ];
    protected $dates = ['consolidated_at'];
    public $timestamps = false;

    public function scopeOfRowColumn($query, $row, $column)
    {
        return $query
            ->where('row_id', $row)
            ->where('column_id', $column);
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsUseList extends Model
{
    //
    protected $fillable = ['row_id', 'col_id', 'list'];

    public function listscript()
    {
        return $this->belongsTo('App\ConsolidationList', 'list');
    }

    public function scopeOfList($query, $list)
    {
        return $query
            ->where('list', $list);
    }

    public function scopeOfRC($query, $row, $column)
    {
        return $query
            ->where('row_id', $row)
            ->where('col_id', $column);
    }
}

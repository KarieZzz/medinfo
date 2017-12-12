<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsolidationRule extends Model
{
    //
    protected $fillable = ['row_id', 'col_id', 'script', 'comment'];

    public function scopeOfRC($query, $row, $column)
    {
        return $query
            ->where('row_id', $row)
            ->where('col_id', $column);
    }

    public function row()
    {
        return $this->belongsTo('App\Row');
    }

    public function column()
    {
        return $this->belongsTo('App\Column', 'col_id');
    }

}

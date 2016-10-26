<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControllingRow extends Model
{
    //
    protected $fillable = [ 'relation', 'form_id', 'table_id', 'row_id', 'first_col', 'count_col', 'rec_id' ];

    public function row()
    {
        return $this->belongsTo('App\Row');
    }

    public function table()
    {
        return $this->belongsTo('App\Table');
    }

    public function scopeOfTable($query, $table)
    {
        return $query
            ->where('table_id', $table);
    }

    public function scopeOfRelation($query, $relation)
    {
        return $query
            ->where('relation', $relation);
    }

}

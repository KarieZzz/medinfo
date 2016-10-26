<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlledRow extends Model
{
    //
    protected $fillable = ['form_id', 'table_id', 'row_id', 'control_scope', 'relation' ];

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

    public function scopeOfControlScope($query, $scope)
    {
        return $query
            ->where('control_scope', $scope);
    }
}

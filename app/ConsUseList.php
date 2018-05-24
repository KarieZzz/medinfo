<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsUseList extends Model
{
    //
    protected $fillable = ['row_id', 'col_id', 'list'];

    public function list()
    {
        return $this->belongsTo('App\UnitList', 'list');
    }

}

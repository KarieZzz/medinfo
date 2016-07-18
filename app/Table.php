<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    //
    protected $fillable = ['table_code', 'table_name', 'medstat_code', 'medinfo_id', 'transposed', 'aggregated_column_id'];

    public function columns()
    {
        return $this->hasMany('App\Column');
    }

    public function rows()
    {
        return $this->hasMany('App\Row');
    }
}

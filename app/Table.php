<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    //

    protected $fillable = ['form_id', 'table_index' , 'table_code',
        'table_name', 'medstat_code', 'medinfo_id', 'transposed',
        'aggregated_column_id', 'deleted'];

    public function columns()
    {
        return $this->hasMany('App\Column');
    }

    public function form()
    {
        return $this->belongsTo('App\Form');
    }

    public function rows()
    {
        return $this->hasMany('App\Row');
    }
}

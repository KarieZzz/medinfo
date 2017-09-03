<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    //
    protected $fillable = ['table_id', 'row_index', 'row_code', 'row_name', 'medstat_code', 'medinfo_id', 'deleted' ];

    public function excluded()
    {
        return $this->hasMany('App\AlbumRowSet');
    }

    public function table()
    {
        return $this->belongsTo('App\Table');
    }

    public function scopeOfTable($query, $table)
    {
        return $query
            ->orderBy('row_index')
            ->where('table_id', $table);
    }

    public function scopeOfTableRowIndex($query, $table, $rowindex)
    {
        return $query
            ->where('table_id', $table)
            ->where('row_index', $rowindex);
    }

    public function scopeOfTableRowCode($query, $table, $rowcode)
    {
        return $query
            ->where('table_id', $table)
            ->where('row_code', $rowcode);
    }

    public function scopeOfTableRowMedstatcode($query, $table, $medstatcode)
    {
        return $query
            ->where('table_id', $table)
            ->where('medstat_code', $medstatcode);
    }


    public function scopeInMedstat($query)
    {
        return $query
            ->whereNotNull('medstat_code');
    }
}

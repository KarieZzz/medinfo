<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    //
    protected $fillable = ['table_id', 'row_index', 'row_code', 'row_name', 'medstat_code', 'medinfo_id', 'deleted', 'medstatnsk_id' ];

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
        return $query->where('table_id', $table);
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
        return $query->whereNotNull('medstat_code');
    }

    public function scopeOfTableRowMedstatNskCode($query, $table, $medstatnsk_id)
    {
        return $query
            ->where('table_id', $table)
            ->where('medstatnsk_id', $medstatnsk_id);
    }
}

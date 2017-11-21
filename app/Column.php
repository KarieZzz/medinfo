<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    //
    const HEADER        = 1;
    const CALCULATED    = 2;
    const DATA          = 4;
    const COMMENT       = 5;

    protected $fillable = [
        'table_id', 'column_index', 'column_name', 'content_type', 'size', 'decimal_count', 'medstat_code', 'medinfo_id',
    ];

    public function excluded()
    {
        return $this->hasMany('App\AlbumColumnSet');
    }

    public function table()
    {
        return $this->belongsTo('App\Table');
    }

    public function calculation()
    {
        return $this->hasOne('App\ColumnCalculation');
    }

    public function getMedinfoContentType()
    {
        switch ($this->content_type) {
            case 1 :
                $contentType = 'header';
                break;
            case 2:
                $contentType = 'calculated';
                break;
            case 4 :
                $contentType = 'data';
                break;
            case 5 :
                $contentType = 'comment';
                break;
            default :
                $contentType = 'undefined';
                break;
        }
        return $contentType;
    }

    public function scopeOfDataType($query)
    {
        return $query->where('content_type', self::DATA);
    }

    public function scopeWhithoutComment($query)
    {
        return $query->where('content_type', '<>' , self::COMMENT);
    }

    public function scopeOfTable($query, $table)
    {
        return $query
            //->orderBy('column_index')
            ->where('table_id', $table);
    }

    public function scopeOfTableColumnIndex($query, $table, $columnindex)
    {
        return $query
            ->where('table_id', $table)
            ->where('column_index', $columnindex);
    }

    public function scopeCalculated($query)
    {
        return $query->where('content_type', self::CALCULATED);
    }

}

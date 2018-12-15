<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    //
    const HEADER        = 1;
    const CALCULATED    = 2;
    const DATA          = 4;
    const SUBHEADER     = 5;

    protected $fillable = [
        'table_id', 'column_index', 'column_code', 'column_name', 'content_type', 'size', 'decimal_count', 'medstat_code', 'medstatnsk_id',
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
            case self::HEADER :
                $contentType = 'header';
                break;
            case self::CALCULATED :
                $contentType = 'calculated';
                break;
            case self::DATA :
                $contentType = 'data';
                break;
            case self::SUBHEADER :
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
        return $query->where('content_type', '<>' , self::SUBHEADER);
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

    public function scopeOfTableColumnCode($query, $table, $code)
    {
        return $query
            ->where('table_id', $table)
            ->where('column_code', $code);
    }

    public function scopeOfTableMedstatCode($query, $table, $mscode)
    {
        return $query
            ->where('table_id', $table)
            ->where('medstat_code', $mscode);
    }

    public function scopeHeader($query)
    {
        return $query->where('content_type', self::HEADER);
    }

    public function scopeSubheader($query)
    {
        return $query->where('content_type', self::SUBHEADER);
    }

    public function scopeHeaders($query)
    {
        return $query
            ->where('content_type', self::HEADER)
            ->orWhere('content_type', self::SUBHEADER);
    }

    public function scopeCalculated($query)
    {
        return $query->where('content_type', self::CALCULATED);
    }

    public function scopeInMedstat($query)
    {
        return $query
            ->whereNotNull('medstat_code');
    }

    public function scopeControlled($query)
    {
        return $query
            ->where('content_type', self::DATA)
            ->orWhere('content_type', self::CALCULATED);
    }

}

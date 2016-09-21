<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    //
    protected $fillable = [
        'table_id', 'column_index', 'column_name', 'content_type', 'size', 'decimal_count', 'medstat_code', 'medinfo_id',
    ];

    public function table()
    {
        return $this->belongsTo('App\Table');
    }

    public function getMedinfoContentType()
    {
        switch ($this->content_type) {
            case 4 :
                $contentType = 'data';
                break;
            case 0 :
                $contentType = 'header';
                break;
            case 5 :
                $contentType = 'comment';
                break;
            case 2:
            case 3:
                $contentType = 'calculated';
                break;
            default :
                $contentType = 'undefined';
                break;
        }
        return $contentType;
    }

    public function scopeOfDataType($query)
    {
        return $query->where('content_type', 4);
    }

    public function scopeOfTable($query, $table)
    {
        return $query
            ->orderBy('column_index')
            ->where('table_id', $table);
    }

}

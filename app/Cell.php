<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cell extends Model
{
    //
    protected $table = 'statdata';
    protected $fillable = [
        'doc_id', 'table_id', 'row_id', 'col_id', 'value',
    ];

    public function scopeOfDTRC($query, $document, $table, $row, $column)
    {
        return $query
            ->where('doc_id', $document)
            ->where('table_id', $table)
            ->where('row_id', $row)
            ->where('col_id', $column);
    }

    public static function countOfCells(int $table)
    {
        $q = "SELECT count(id) cell_count FROM statdata WHERE table_id = $table";
        return \DB::selectOne($q)->cell_count;
    }

}

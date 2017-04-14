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

    public function table()
    {
        return $this->belongsTo('App\Table');
    }

    public function row()
    {
        return $this->belongsTo('App\Row');
    }

    public function column()
    {
        return $this->belongsTo('App\Column', 'col_id');
    }

    public function scopeOfDTRC($query, $document, $table, $row, $column)
    {
        return $query
            ->where('doc_id', $document)
            ->where('table_id', $table)
            ->where('row_id', $row)
            ->where('col_id', $column);
    }

    public function scopeOfDRC($query, $document, $row, $column)
    {
        return $query
            ->where('doc_id', $document)
            ->where('row_id', $row)
            ->where('col_id', $column);
    }

    public function scopeOfDTR($query, $document, $table, $row)
    {
        return $query
            ->where('doc_id', $document)
            ->where('table_id', $table)
            ->where('row_id', $row);
    }

    public function scopeOfDocument($query, $document)
    {
        return $query
            ->where('doc_id', $document);
    }

    public function scopeOfDocumentTable($query, $document, $table)
    {
        return $query
            ->where('doc_id', $document)
            ->where('table_id', $table);
    }

    public static function countOfCellsByTable(int $table)
    {
        $q = "SELECT count(id) cell_count FROM statdata WHERE table_id = $table";
        return \DB::selectOne($q)->cell_count;
    }

    public static function countOfCellsByRow(int $row)
    {
        $q = "SELECT count(id) cell_count FROM statdata WHERE row_id = $row";
        return \DB::selectOne($q)->cell_count;
    }

    public static function countOfCellsByColumn(int $column)
    {
        $q = "SELECT count(id) cell_count FROM statdata WHERE col_id = $column";
        return \DB::selectOne($q)->cell_count;
    }
}

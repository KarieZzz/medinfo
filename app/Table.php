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

    public function excluded()
    {
        return $this->hasMany('App\AlbumTableSet');
    }

    public function scopeOfForm($query, $form)
    {
        return $query
            ->orderBy('table_index')
            ->where('form_id', $form);
    }

    public function scopeOfFormTableCode($query, $form, $table_code)
    {
        return $query
            ->where('form_id', $form)
            ->where('table_code', $table_code);
    }

    public static function editedTables(int $document)
    {
        $editedtables = \DB::table('statdata')
            ->join('documents', 'documents.id' ,'=', 'statdata.doc_id')
            ->leftJoin('tables', 'tables.id', '=', 'statdata.table_id')
            ->where('documents.id', $document)
            ->where('tables.deleted', 0)
            ->groupBy('statdata.table_id')
            ->pluck('statdata.table_id');
        return $editedtables;
    }



}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    //

    protected $fillable = [
        'form_id',
        'table_index' ,
        'table_code',
        'table_name',
        'medstat_code',
        'medinfo_id',
        'transposed',
        'aggregated_column_id',
        'deleted',
        'medstatnsk_id',
    ];

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
            //->orderBy('table_index')
            ->where('form_id', $form);
    }

    public function scopeOfFormTableCode($query, $form, $table_code)
    {
        return $query
            ->where('form_id', $form)
            ->where('table_code', $table_code);
    }

    public function scopeOfFormTableIndex($query, $form, $tableindex)
    {
        return $query
            ->where('form_id', $form)
            ->where('table_index', $tableindex);
    }

    public function scopeOfMedstat($query)
    {
        return $query->whereNotNull('medstat_code');
    }

    public function scopeOfMedstatCode($query, $code)
    {
        return $query->where('medstat_code', $code);
    }

    public function scopeHasMedstatNskId($query)
    {
        return $query->whereNotNull('medstatnsk_id');
    }

    public function scopeOfMedstatNsk($query, $id)
    {
        return $query->where('medstatnsk_id', $id);
    }

    public static function editedTables(int $document, int $album)
    {
        $editedtables = \DB::table('statdata')
            ->join('documents', 'documents.id' ,'=', 'statdata.doc_id')
            ->leftJoin('tables', 'tables.id', '=', 'statdata.table_id')
            ->leftJoin('album_tables', 'album_tables.table_id', '<>', 'statdata.table_id')
            ->where('documents.id', $document)
            ->where('album_tables.album_id', $album)
            //->where('tables.deleted', 0)
            ->groupBy('statdata.table_id')
            ->pluck('statdata.table_id');
        return $editedtables;
    }



}

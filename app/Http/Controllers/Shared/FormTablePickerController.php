<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class FormTablePickerController extends Controller
{
    //
    public function fetchTables(int $form)
    {
        return \App\Table::OfForm($form)->orderBy('table_index')->with('form')->get();
    }

    public function fetchActualRows(int $table)
    {
        $default_album = \App\Album::Default()->first();
        $default_album_id = $default_album ? $default_album->id : config('medinfo.default_album');
        return \App\Row::OfTable($table)->with('table')->whereDoesntHave('excluded', function ($query) use($default_album_id) {
            $query->where('album_id', $default_album_id);
        })->orderBy('row_index')->get();
    }

    public function fetchDataTypeColumns(int $table)
    {
        $default_album = \App\Album::Default()->first();
        $default_album_id = $default_album ? $default_album->id : config('medinfo.default_album');
        return \App\Column::OfTable($table)->OfDataType()->whereDoesntHave('excluded', function ($query) use($default_album_id) {
            $query->where('album_id', $default_album_id);
        })->orderBy('column_index')->get();
    }

}

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
        $default_album = Album::Default()->first()->id;
        return Row::OfTable($table)->with('table')->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album);
        })->orderBy('row_index')->get();
    }

    public function fetchDataTypeColumns(int $table)
    {
        $default_album = Album::Default()->first()->id;
        return Column::OfTable($table)->OfDataType()->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album);
        })->orderBy('column_index')->get();
    }

}

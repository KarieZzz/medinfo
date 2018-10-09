<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ClearNECells extends Controller
{
    //
    public function index()
    {
        $query = "SELECT count(v.id) FROM statdata v
          JOIN documents d ON d.id = v.doc_id
          JOIN forms f ON f.id = d.form_id 
          JOIN noteditable_cells ne ON ne.row_id = v.row_id AND ne.column_id = v.col_id";
        $dirty_cells = \DB::select($query)[0]->count;
        $forms = \App\Form::orderBy('form_code')->get(['id', 'form_code', 'form_name']);
        $periods = \App\Period::all();
        $albums = \App\Album::all()->sortBy('album_name');
        return view('jqxadmin.system.noteditableCellClear', compact('dirty_cells', 'forms', 'periods', 'albums'));
    }

    public function clearNECells(Request $request)
    {
        $this->validate($request, [
                'album' => 'required|integer',
                'period' => 'required|integer',
                'formids' => 'required',
                'selectedallforms' => 'required|in:1,0',
            ]
        );
        $a = $request->album;
        $p = $request->period;
        $f = $request->formids;
        $query = "DELETE FROM statdata WHERE id IN (
            SELECT v.id FROM statdata v
            JOIN documents d ON d.id = v.doc_id
            JOIN forms f ON f.id = d.form_id 
            JOIN noteditable_cells ne ON ne.row_id = v.row_id AND ne.column_id = v.col_id
            WHERE d.period_id = $p
            AND d.album_id = $a
            AND f.id IN ($f));";
        $cleared = \DB::delete($query);
        return view('jqxadmin.system.noteditableCellClearResult', compact('cleared'));
    }


}

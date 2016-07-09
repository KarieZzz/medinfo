<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Row;

class StructureRowController extends Controller
{
    //
    public function showrows()
    {
        $rows = Row::where('table_id', 10)->paginate(10);
        //$rows = Row::paginate(10);
        //return $rows;
        return view('structure.rows', compact('rows'));
    }

    public function editrow(Row $row)
    {
        return view('structure.editrow', compact('row'));
    }

    public function updaterow(Request $request, Row $row)
    {
        $this->validate($request, [
                'form_name' => 'required|max:256',
                'form_code' => 'required',
                'medstat_code' => 'min:2',
            ]
        );
        $row->update($request->all());

        \Session::flash('flash_message', 'Запись сохранена');
        return back();
    }

}

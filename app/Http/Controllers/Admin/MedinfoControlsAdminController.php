<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\ControlledRow;
use App\ControllingRow;
use App\ControlledColumn;

class MedinfoControlsAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.micontrols', compact('forms'));
    }

    public function fetchControlledRows(int $table, int $scope)
    {
        return ControlledRow::ofTable($table)->ofControlScope($scope)->with('table')->with('row')->get();
    }

    public function fetchControllingRows(int $table, int $relation)
    {
        return ControllingRow::ofTable($table)->ofRelation($relation)->with('table')->with('row')->get();
    }

    public function fetchColumns(int $firstcol, int $countcol)
    {
        return ControlledColumn::where('rec_id','>=' ,$firstcol)->where('rec_id', '<', $firstcol + $countcol)->get();
    }
}

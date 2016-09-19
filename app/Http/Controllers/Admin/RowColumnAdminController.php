<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\Table;
use App\Row;
use App\Column;

class RowColumnAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        $tables = Table::orderBy('form_id')->orderBy('table_index')->get(['id', 'form_id', 'table_code']);
        return view('jqxadmin.rowcolumns', compact('forms', 'tables'));
    }

    public function fetchRows(int $table)
    {
        return Row::OfTable($table)->with('table')->get();
    }

    public function fetchColumns(int $table)
    {
        return Column::OfTable($table)->with('table')->get();
    }

}

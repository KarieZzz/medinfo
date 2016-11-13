<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\Table;
use App\Row;
use App\Column;
use App\Cell;

class RowColumnAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        //$forms = Form::orderBy('form_index')->with('tables')->get(['id', 'form_code']);
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        //$tables = Table::orderBy('form_id')->orderBy('table_index')->get(['id', 'form_id', 'table_code']);
        //return view('jqxadmin.rowcolumns', compact('forms', 'tables'));
        return view('jqxadmin.rowcolumns', compact('forms'));
    }

    public function fetchTables(int $form)
    {
        return Table::OfForm($form)->with('form')->get();
    }

    public function fetchRows(int $table)
    {
        //return Row::OfTable($table)->with('table')->get();
        return Row::OfTable($table)->with('table')->with(['excluded' => function ($query) {
            $query->where('album_id', 1);
        }])->get();
    }

    public function fetchColumns(int $table)
    {
        return Column::OfTable($table)->with('table')->get();
    }

    public function rowUpdate(Row $row, Request $request)
    {
        $this->validate($request, [
                'row_index' => 'integer',
                'row_name' => 'required|max:128',
                'row_code' => 'required|max:16',
                'medstat_code' => 'digits:3',
                'medinfo_id' => 'integer',
            ]
        );
        $row->row_index = $request->row_index;
        $row->row_code = $request->row_code;
        $row->row_name = $request->row_name;
        $row->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $row->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        $result = [];
        try {
            $row->save();
            $result = ['message' => 'Запись id ' . $row->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == '23505'){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование данных.'];
            }
        }
        return $result;
    }

    public function rowStore(Request $request)
    {
        $this->validate($request, [
                'table_id' => 'required|exists:tables,id',
                'row_index' => 'integer',
                'row_name' => 'required|max:128',
                'row_code' => 'required|max:16',
                'medstat_code' => 'digits:3',
                'medinfo_id' => 'integer',
            ]
        );
        $newrow = new Row;
        $newrow->table_id = $request->table_id;
        $newrow->row_index = $request->row_index;
        $newrow->row_code = $request->row_code;
        $newrow->row_name = $request->row_name;
        $newrow->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $newrow->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        try {
            $newrow->save();
            return ['message' => 'Новая запись создана. Id:' . $newrow->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирующиеся значения.';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function rowDelete(Row $row)
    {
        $cell_count = Cell::countOfCellsByRow($row->id);
        if ($cell_count == 0) {
            $row->delete();
            return ['message' => 'Удалена строка Id' . $row->id ];
        } else {
            return ['error' => 422, 'message' => 'Строка Id' . $row->id . ' содержит данные. Удаление невозможно.' ];
        }
    }

    public function columnUpdate(Column $column, Request $request)
    {
        $this->validate($request, [
                'column_index' => 'integer',
                'column_name' => 'required|max:128',
                'content_type' => 'integer',
                'size' => 'integer',
                'decimal_count' => 'integer',
                'medstat_code' => 'digits:2',
                'medinfo_id' => 'integer',
            ]
        );
        $column->column_index = $request->column_index;
        $column->column_name = $request->column_name;
        $column->content_type = $request->content_type;
        $column->size = $request->size;
        $column->decimal_count = $request->decimal_count;
        $column->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $column->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        $result = [];
        try {
            $column->save();
            $result = ['message' => 'Запись id ' . $column->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == '23505'){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование данных.'];
            }
        }
        return $result;
    }

    public function columnStore(Request $request)
    {
        $this->validate($request, [
                'table_id' => 'required|exists:tables,id',
                'column_index' => 'integer',
                'column_name' => 'required|max:128',
                'content_type' => 'integer',
                'size' => 'integer',
                'decimal_count' => 'integer',
                'medstat_code' => 'digits:2',
                'medinfo_id' => 'integer',
            ]
        );
        $newcolumn = new Column;
        $newcolumn->table_id = $request->table_id;
        $newcolumn->column_index = $request->column_index;
        $newcolumn->column_name = $request->column_name;
        $newcolumn->content_type = $request->content_type;
        $newcolumn->size = $request->size;
        $newcolumn->decimal_count = $request->decimal_count;
        $newcolumn->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $newcolumn->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        try {
            $newcolumn->save();
            return ['message' => 'Новая запись создана. Id:' . $newcolumn->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирующиеся значения.';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function columnDelete(Column $column)
    {
        $cell_count = Cell::countOfCellsByRow($column->id);
        if ($cell_count == 0) {
            $column->delete();
            return ['message' => 'Удалена графа Id' . $column->id ];
        } else {
            return ['error' => 422, 'message' => 'Графа Id' . $column->id . ' содержит данные. Удаление невозможно.' ];
        }
    }

}

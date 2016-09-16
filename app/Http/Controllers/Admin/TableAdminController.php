<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\Table;
use App\Cell;


class TableAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.tables', compact('forms'));
    }

    public function fetchTables()
    {
        return Table::orderBy('form_id')->orderBy('table_index')->with('form')->get();
        //return Form::all();
    }

    public function store(Request $request)
    {
        //dd($request->id);
        $this->validate($request, [
                'form_id' => 'required|exists:forms,id',
                'table_name' => 'required',
                'table_code' => 'required',
            ]
        );
        try {
            $newtable = Table::create($request->all());
            return ['message' => 'Новая запись создана. Id:' . $newtable->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            switch ($errorCode) {
                case 7:
                    $message = 'Запись не сохранена. В форме не должно быть двух и более таблиц с повторяющимися кодами.';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function update(Request $request)
    {
        $this->validate($request, [
                'table_name' => 'required',
            ]
        );
        $table = Table::find($request->id);
        $table->form_id = $request->form_id;
        $table->table_index = $request->table_index;
        $table->table_code = $request->table_code;
        $table->table_name = $request->table_name;
        $table->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $table->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        $table->transposed = $request->transposed;
        $result = [];
        try {
            $table->save();
            $result = ['message' => 'Запись id ' . $table->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == 7){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование формы/кода таблицы.'];
            }
        }
        return $result;
    }

    public function delete(Table $table)
    {
        $cell_count = Cell::countOfCells($table->id);
        if ($cell_count == 0) {
            $table->delete();
            return ['message' => 'Удалена таблица Id' . $table->id ];
        } else {
            return ['error' => 422, 'message' => 'Таблица Id' . $table->id . ' содержит данные. Удаление невозможно.' ];
        }
    }
}

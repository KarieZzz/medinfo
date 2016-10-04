<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Form;
use App\Table;
use App\NECell;
use App\NECellCondition;
use App\UnitGroup;
use App\Medinfo\TableEditing;

class NECellAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list()
    {
        $forms = Form::orderBy('form_index')->with('tables')->get(['id', 'form_code']);
        $tables = Table::orderBy('form_id')->orderBy('table_index')->get(['id', 'form_id', 'table_code']);
        $conditions = NECellCondition::all();
        return view('jqxadmin.necells', compact('forms', 'tables', 'conditions'));

    }

    public function conditions()
    {
        $groups = UnitGroup::all();
        return view('jqxadmin.conditions', compact('groups'));
    }

    public function fetchGrid(Table $table)
    {
        return TableEditing::fetchDataForTableRenedering($table, 'checkbox', false);
    }

    public function fetchValues(Table $table)
    {
        $rows = $table->rows->sortBy('row_index');
        $cols = $table->columns->sortBy('column_index');
        $data = array();
        $i=0;
        foreach ($rows as $r) {
            $row = array();
            $row['id'] = $r->id;
            foreach($cols as $col) {
                $contentType = $col->getMedinfoContentType();
                if ($contentType == 'header') {
                    if ($col->column_index == 1) {
                        $row[$col->id] = $r->row_name;
                    } elseif ($col->column_index == 2) {
                        $row[$col->id] = $r->row_code;
                    }
                } elseif ($contentType == 'data') {
                    $row[$col->id] = NECell::OfRC($r->id, $col->id)->exists();
                }
            }
            $data[$i] = $row;
            $i++;
        }
        return $data;
    }

    public function fetchConditions()
    {
        return NECellCondition::with('group')->get();
    }

    public function fetchCellsCondition(Request $request)
    {
        return 1;
    }

    public function toggleCellRange($range, $noedit, $condition)
    {
        $coordinates = explode(',', $range);
        foreach ($coordinates as $coordinate) {
            //$rc = explode('_', $coordinate);
            //$row_id = $rc[0];
            //$column_id = $rc[1];
            $this->toggleCellState($coordinate, $noedit, $condition);
        }
        return ['message' => 'Статус ячеек в выделенном диапазоне изменен'];
    }

    public function toggleCellState($row_column, $newstate, $condition)
    {
        $rc = explode('_', $row_column);
        $row_id = $rc[0];
        $column_id = $rc[1];
        if ($newstate) {
            $ne = NECell::firstOrCreate([ 'row_id' => $row_id, 'column_id' => $column_id, 'condition_id' => $condition ]);
        }  else {
            $ne = NECell::where('row_id', $row_id)->where('column_id', $column_id)->delete();
        }
        return ['message' => 'Статус ячейки изменен'];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'condition_name' => 'required',
                'group_id' => 'required|exists:unit_groups,id',
            ]
        );
        try {
            $newcondition = NECellCondition::create($request->all());
            return ['message' => 'Новая запись создана. Id:' . $newcondition->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == 7){
                return ['error' => 422, 'message' => 'Новая запись не создана. Существует Условие с такими же параметрами.'];
            }
        }
    }

    public function update(NECellCondition $condition, Request $request)
    {
        $this->validate($request, [
                'condition_name' => 'required',
                'group_id' => 'required|exists:unit_groups,id',
            ]
        );
        $condition->condition_name = $request->condition_name;
        $condition->group_id = $request->group_id;
        $result = [];
        try {
            $condition->save();
            $result = ['message' => 'Запись id ' . $condition->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == 7){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование наименования/группы условия.'];
            }
        }
        return $result;
    }

    public function delete(NECellCondition $condition)
    {
        $id = $condition->id;
        if ($condition->delete()) {
            return ['message' => 'Удален удалено условие закрещивания ячеек Id ' . $id ];
        } else {
            return ['error' => 422, 'message' => 'Ошибка удаления' ];
        }
    }

}

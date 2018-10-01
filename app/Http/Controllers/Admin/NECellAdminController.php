<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Album;
use App\Form;
use App\Table;
use App\NECell;
use App\NECellCondition;
use App\UnitList;
use App\Medinfo\TableEditing;

class NECellAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
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
        $lists = UnitList::all();
        return view('jqxadmin.conditions', compact('lists'));
    }

    public function fetchGrid(Table $table)
    {
        //$general_album = Album::General()->first(['id']);
        $column_type = 'checkbox';
        return TableEditing::tableRender($table, $column_type);
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

    public function fetchCellsCondition($range)
    {
        $conditions = [];
        $coordinates = explode(',', $range);
        foreach ($coordinates as $coordinate) {
            $conditions[] = $this->getCellCondition($coordinate);
        }
        $conditions = array_values(array_unique($conditions));
        return $conditions;
    }

    public function toggleCellRange($range, $noedit, $condition)
    {
        $coordinates = explode(',', $range);
        foreach ($coordinates as $coordinate) {
            $this->toggleCellState($coordinate, $noedit, $condition);
        }
        return ['message' => 'Статус ячеек в выделенном диапазоне изменен'];
    }

    public function toggleCellState($row_column, $newstate, $condition)
    {
        $rc = explode('_', $row_column);
        $row_id = $rc[0];
        $column_id = $rc[1];
        NECell::OfRC($row_id, $column_id)->delete();
        if ($newstate) {
            $ne = NECell::firstOrCreate([ 'row_id' => $row_id, 'column_id' => $column_id, 'condition_id' => $condition ]);
        }
        return ['message' => 'Статус ячейки изменен'];
    }

    public function getCellCondition($row_column)
    {
        $rc = explode('_', $row_column);
        $row_id = $rc[0];
        $column_id = $rc[1];
        $ne = NECell::OfRC($row_id, $column_id)->with('condition')->first();
        return $ne->condition_id == 0 ? null : $ne->condition->condition_name;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'condition_name' => 'required',
                'group_id' => 'required|exists:unit_groups,id',
                'exclude' => 'required|in:1,0',
            ]
        );
        try {
            $request->exclude = $request->exclude == 1 ? true : false;
            $newcondition = NECellCondition::create($request->all());
            return ['message' => 'Новая запись создана. Id:' . $newcondition->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == '23505'){
                return ['error' => 422, 'message' => 'Новая запись не создана. Существует Условие с такими же параметрами.'];
            }
        }
    }

    public function update(NECellCondition $condition, Request $request)
    {
        $this->validate($request, [
                'condition_name' => 'required',
                'group_id' => 'required|exists:unit_lists,id',
                'exclude' => 'required|in:1,0',
            ]
        );
        $condition->condition_name = $request->condition_name;
        $condition->group_id = $request->group_id;
        $condition->exclude = $request->exclude == 1 ? true : false;
        $result = [];
        try {
            $condition->save();
            $result = ['message' => 'Запись id ' . $condition->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            if($errorCode == '23505'){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование наименования/группы условия.'];
            }
        }
        return $result;
    }

    public function delete(NECellCondition $condition)
    {
        $id = $condition->id;
        if ($condition->delete()) {
            // Связанные c условием закрещенные ячейки удаляем тоже
            $deletedNECells = NECell::where('condition_id', $id)->delete();
            return ['message' => 'Удалено условие закрещивания ячеек Id ' . $id ];
        } else {
            return ['error' => 422, 'message' => 'Ошибка удаления' ];
        }
    }

}

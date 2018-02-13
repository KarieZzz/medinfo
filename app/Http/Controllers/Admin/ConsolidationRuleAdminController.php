<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\ConsolidationRule;

class ConsolidationRuleAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $forms = \App\Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.consolidationrules', compact('forms'));
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules());
        $rule = ConsolidationRule::firstOrNew([ 'row_id' => $request->row, 'col_id' => $request->column,  ]);
        $rule->script = $request->rule;
        $rule->save();
        return ['message' => 'Новая запись создана/сохранена. Id:' . $rule->id, 'id' => $rule->id, ];
    }

    public function destroy($row, $column)
    {
        $rule = ConsolidationRule::OfRC($row, $column)->first();
        if (!is_null($rule)) {
            $rule->delete();
            return ['message' => 'Запись удалена. Id:' . $rule->id, 'id' => $rule->id, ];
        }
        return ['message' => 'Запись удалена.' ];
    }

    public function getTableStruct(\App\Table $table)
    {
        $struct = \App\Medinfo\TableEditing::tableRender($table);
        return $struct;
    }

    public function getRules(\App\Table $table) {
        $rows = $table->rows->sortBy('row_index');
        $cols = $table->columns->sortBy('column_index');
        $data = array();
        $i=0;
        foreach ($rows as $r) {
            $row = array();
            $row['id'] = $r->id;
            foreach($cols as $col) {
                if ($col->content_type == \App\Column::HEADER) {
                    if ($col->column_index == 1) {
                        $row[$col->id] = $r->row_name;
                    } elseif ($col->column_index == 2) {
                        $row[$col->id] = $r->row_code;
                    }
                } elseif ($col->content_type == \App\Column::DATA) {
                    if(!is_null($rule = ConsolidationRule::OfRC($r->id, $col->id)->first())) {
                        $row[$col->id] = $rule->script;
                    } else {
                        $row[$col->id] = '';
                    }
                }
            }
            $data[$i] = $row;
            $i++;
        }
        return $data;
    }

    protected function validateRules()
    {
        return [
            'rule' => 'required|min:1|max:512',
            'comment' => 'max:128',
            'row' => 'required|integer|exists:rows,id',
            'column' => 'required|integer|exists:columns,id',
        ];
    }
}

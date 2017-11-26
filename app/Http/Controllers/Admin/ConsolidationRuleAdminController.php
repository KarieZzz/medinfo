<?php

namespace App\Http\Controllers\Admin;

use App\ConsolidationRule;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConsolidationRuleAdminController extends Controller
{
    //
    public function index()
    {
        $forms = \App\Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.consolidationrules', compact('forms'));
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
}

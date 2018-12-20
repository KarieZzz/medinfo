<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Document;
use App\Table;
use App\Cell;
use App\Medinfo\Consolidation\ConsolidationRuleHelper;

class DocumentConsolidationController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function consolidateDocument(Document $document)
    {
        $tables = $document->form->tables->sortBy('table_index');
        $cell_affected = 0;
        foreach ($tables as $table) {
            $result = $this->consolidatePivotTable($document, $table);
            $cell_affected += $result['cell_affected'];
        }
        return ['consolidated' => true, 'cell_affected' => $cell_affected];
    }

    public function consolidatePivotTableByRule(Document $document, Table $table)
    {
        set_time_limit(240);
        $rules = ConsolidationRuleHelper::getTableRules($table);
        //dd($rules);
        $cell_affected = 0;
        // очищаем таблицу перед заполнением
        $cell_truncated = Cell::Where('doc_id', $document->id)->Where('table_id', $table->id)->delete();
        foreach ($rules as $rule) {
            //dd($rule);
            $value = ConsolidationRuleHelper::evaluateRule($rule, $document, $table);
            if (is_numeric($value)) {
                if ($value == 0) {
                    //echo "Полученное значение 0, в БД записано null";
                    $value = null;
                }
                //dd($value);
                $cell = Cell::firstOrCreate(['doc_id' => $document->id, 'table_id' => $table->id, 'row_id' => $rule->row_id,
                    'col_id' => $rule->col_id, 'value' => $value]);
                //dd($cell);
                if ($cell) {
                    $cell_affected++;
                }
            }
        }
        return ['consolidated' => true, 'cell_affected' => $cell_affected, 'cell_truncated' => $cell_truncated ];
    }

    public function consolidatePivoteTableByRuleAndUnitlist(Document $document, Table $table)
    {
        set_time_limit(240);
        $rules = ConsolidationRuleHelper::getTableRules($table);
        $cell_affected = 0;
        // очищаем таблицу перед заполнением
        $cell_truncated = Cell::Where('doc_id', $document->id)->Where('table_id', $table->id)->delete();
        foreach ($rules as $rule) {
            //dd($rule);
            $value = ConsolidationRuleHelper::evaluateRule($rule, $document, $table);
            if (is_numeric($value)) {
                if ($value == 0) {
                    //echo "Полученное значение 0, в БД записано null";
                    $value = null;
                }
                //dd($value);
                $cell = Cell::firstOrCreate(['doc_id' => $document->id, 'table_id' => $table->id, 'row_id' => $rule->row_id,
                    'col_id' => $rule->col_id, 'value' => $value]);
                //dd($cell);
                if ($cell) {
                    $cell_affected++;
                }
            }
        }
        return ['consolidated' => true, 'cell_affected' => $cell_affected, 'cell_truncated' => $cell_truncated ];
    }

}

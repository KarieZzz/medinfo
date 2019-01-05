<?php

namespace App\Http\Controllers\Admin;

use App\ConsolidationCalcrule;
use App\Medinfo\UnitTree;
use App\Unit;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Document;
use App\Table;
use App\Cell;
use App\Medinfo\Consolidation\ConsolidationRuleHelper;
use App\ConsUseRule;
use App\ConsUseList;

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
        $cell_truncated = Cell::where('doc_id', $document->id)->Where('table_id', $table->id)->delete();
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
        // очищаем таблицу перед заполнением
        $cell_truncated = Cell::where('doc_id', $document->id)->where('table_id', $table->id)->delete();
        $rows = $table->rows->sortBy('row_index');
        $cols = $table->columns->sortBy('column_index');
        $scripts = [];
        $lists = [];
        $cell_affected = 0;
        $unitlist_empty = 0;
        // если "пустой" список - выбираем все МО с текущего уровня
        $lists[0] = UnitTree::getIds($document->ou_id)->whereIn('node_type', [3,4])->pluck('id')->toArray();
        asort($lists[0]);
        //dd($lists[0]);
        foreach ($rows as $r) {
            foreach($cols as $col) {
                $units = [];
                if ($col->content_type == \App\Column::DATA) {
                    $list_using = ConsUseList::OfRC($r->id, $col->id)->first();
                    if (!is_null($list_using)) {
                        if (array_key_exists($list_using->list, $lists)) {
                            $units = $lists[$list_using->list];
                        } else {
                            $units = json_decode($list_using->listscript->properties, true);
                            $lists[$list_using->list] = $units;
                        }
                    }
                    $rule_using = ConsUseRule::OfRC($r->id, $col->id)->first();
                    if (!is_null($rule_using)) {
                        if (array_key_exists($rule_using->script, $scripts)) {
                            $evaluator = $scripts[$rule_using->script]['evaluator'];
                            $evaluator->setUnitList([]);
                        } else {
                            $ptree = unserialize(base64_decode($rule_using->rulescript->ptree));
                            $props = json_decode($rule_using->rulescript->properties, true);
                            $evaluator = \App\Medinfo\DSL\Evaluator::invoke($ptree, $props, $document);
                            $scripts[$rule_using->script]['evaluator'] = $evaluator;
                        }
                        $evaluator->setUnitList($units);
                        $evaluator->clearCalculationLog();
                        $evaluator->makeConsolidation();
                        $value = $evaluator->evaluate();
                        if ($value) {
                            Cell::firstOrCreate(['doc_id' => $document->id, 'table_id' => $table->id, 'row_id' => $r->id, 'col_id' => $col->id, 'value' => $value]);
                            $cell_affected++;
                        }
                        //dd($evaluator->calculationLog);
                        ConsolidationRuleHelper::logConsolidation($evaluator->calculationLog, $document->id, $r->id, $col->id);
                    }
                }
            }
        }
        //return ['consolidated' => true, 'cell_affected' => $cell_affected, 'cell_truncated' => $cell_truncated ];
        dd( ['consolidated' => true, 'cell_affected' => $cell_affected, 'cell_truncated' => $cell_truncated, 'unitlist_empty' => $unitlist_empty ]);
    }

}

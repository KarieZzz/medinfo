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
            $cell_affected += $this->consolidatePivotTable($table, $document);
        }
        return ['consolidated' => true, 'cell_affected' => $cell_affected];
    }

    public function consolidatePivotTable(Table $table, Document $document)
    {
        $rules = ConsolidationRuleHelper::getTableRules($table);
        $cell_affected = 0;
        foreach ($rules as $rule) {
            $value = ConsolidationRuleHelper::evaluateRule($rule, $document, $table);
            $cell = Cell::firstOrCreate(['doc_id' => $document->id, 'table_id' => $table->id, 'row_id' => $rule['row'], 'col_id' => $rule['column']]);
            if (is_numeric($value)) {
                if ($value == 0) {
                    //echo "Полученное значение 0, в БД записано null";
                    $cell->value = null;
                }
                else {
                    //echo "Получено значение отличное от нуля, в БД записано числовое значение";
                    $cell->value = $value;
                }
            }
            else {
                //echo "Получено нечисловое значение, в БД записано null";
                $cell->value = null;
            }
            $result = $cell->save();
            if ($result) {
                $cell_affected++;
            }
        }

        return $cell_affected;
    }
}

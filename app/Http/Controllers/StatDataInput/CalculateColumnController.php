<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Medinfo\DSL\CalculationFunctionLexer;
use App\Medinfo\DSL\CalculationFunctionParser;
use App\Cell;
use App\Column;
use App\Document;
use App\Medinfo\DSL\EvaluatorExample;
use App\Row;
use App\Table;

class CalculateColumnController extends Controller
{
    //
    public $document;
    public $table;

    public function calculate(Document $document, Table $table)
    {
        $this->document = $document;
        $this->table = $table;
        $calcColumns = Column::OfTable($table->id)->Calculated()->orderBy('column_index')->with('calculation')->get();
        $result['errors'] = [];
        $result['calculations'] = [];
        if ($calcColumns->count() === 0) {
            return 'Нет расчитываемых ячеек';
        }
        foreach ($calcColumns as $c) {
            try {
                $input = $c->calculation->formula;
            } catch (\Exception $e) {
                $result['errors'][] = 'Для рассчитываемой графы ' . $c->column_index . ' не задана формула расчета';
                break;
            }
            $lexer = new CalculationFunctionLexer($input);
            $tokenstack = $lexer->getTokenStack();
            $parcer = new CalculationFunctionParser($tokenstack);
            $pt = $parcer->expression();
            $replacementStacks = json_decode($c->calculation->compiled);
            foreach ($replacementStacks->cellstack as $el) {
                $header = '';
                if ($replacementStacks->vector === 'rows') {
                    if ($row = Row::find($el[0]->r)) {
                        $header = $row->row_name;
                    }
                } elseif ($replacementStacks->vector === 'columns') {
                    if ($column = Column::find($el[0]->c)) {
                        $header = $column->column_name;
                    }
                }
                $i = 0;
                foreach ($el as $cellLink) {
                    if ($cell = Cell::OfDRC($this->document->id, $cellLink->r, $cellLink->c)->first(['value'])) {
                        $v = $cell->value;
                    } else {
                        $v = 0;
                    }
                    $node = $parcer->celladressStack->offsetGet($i);
                    $node->type = CalculationFunctionLexer::NUMBER;
                    $node->content = $v;
                    $i++;
                }
                $eval = new EvaluatorExample($pt);
                $calculated = $eval->evaluate();
/*                switch (true) {
                    case $calculated == 0 :
                        $color = 'blue';
                        break;
                    case $calculated < 250 :
                    case $calculated > 350 :
                        $color = 'red';
                        break;
                    case $calculated >= 250 && $calculated <= 350:
                        $color = 'green';
                        break;
                    default:
                        $color = 'black';
                }*/
                try {
                    $cell = Cell::firstOrCreate(['doc_id' => $document->id, 'table_id' => $table->id, 'row_id' => $el[0]->r, 'col_id' => $c->id, ]);
                    if ($calculated !== 0) {
                        $cell->value = $calculated;
                    } else {
                        $cell->value = null;
                    }
                    $cell->save();
                } catch (\Exception $e) {
                    $result['errors'][] = 'Ошибка записи данных в графу' . $c->column_index . ' по строке ' . Row::find($el[0]->r)->row_code;
                }
            }
        }
        return $result;
    }
}
<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Medinfo\Calculation\CalculationFunctionLexer;
use App\Medinfo\Calculation\CalculationFunctionParser;
use App\Cell;
use App\Column;
use App\Document;
use App\Medinfo\Calculation\Evaluator;
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
        //dd($calcColumns[0]->calculation);
        //dd($calcColumns->count());
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

            //echo "<h2>Работа койки (т.3100) ($input)</h2>";
            //echo "<h2>Оборот койки (т.3100) ($input)</h2>";
            //dd($c->calculation);
            $lexer = new CalculationFunctionLexer($input);
            $tokenstack = $lexer->getTokenStack();
            //dd($lexer);
            //$tokenstack->rewind();
            //dd($tokenstack);
            $parcer = new CalculationFunctionParser($tokenstack);
            $pt = $parcer->expression();
            //dd($pt);
            //dd($parcer->celladressStack);
            $replacementStacks = json_decode($c->calculation->compiled);
            //dd($replacementStacks);
            foreach ($replacementStacks->cellstack as $el) {
                if ($replacementStacks->vector === 'rows') {
                    $header = Row::find($el[0]->r)->row_name;
                } elseif ($replacementStacks->vector === 'columns') {
                    $header = Column::find($el[0]->c)->column_name;
                }
                //dd($header);
                $i = 0;
                foreach ($el as $cellLink) {
                    if ($cell = Cell::OfDRC($this->document->id, $cellLink->r, $cellLink->c)->first(['value'])) {
                        $v = $cell->value;
                    } else {
                        $v = 0;
                    }
                    //dd($v);
                    $node = $parcer->celladressStack->offsetGet($i);
                    $node->type = CalculationFunctionLexer::NUMBER;
                    $node->content = $v;
                    $i++;
                }
                //dd($node);
                ///dd($parcer->celladressStack);
                //dd($pt);
                $eval = new Evaluator($pt);
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
                //$formated = number_format($eval->evaluate(), 2, ',', ' ');
                //echo "<p>$header: <span style='color: $color'><strong>$formated</strong></span></p>";
                $result['calculations'][] = ['r' => $el[0]->r, 'c' => $c->id, 'v' => $calculated];
            }
        }
        return $result;
    }
}

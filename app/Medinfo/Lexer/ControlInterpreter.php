<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2016
 * Time: 9:38
 */

namespace App\Medinfo\Lexer;
use App\Document;
use App\Period;
use App\Form;
use App\Row;
use App\Column;
use App\Table;
use App\Cell;

class ControlInterpreter
{
    public $root;
    public $unitScope; // область приложения функции (группы учреждений)
    public $readableFormula; // отображение формулы контроля в более удобочитаемом виде
    public $pad = ' ';
    public $results; // Протокол выполнения функции
    public $iterationMode; // режим перебора - без перебора(null) по строкам(1) и графам(2) при внутритабличном контроле
    public $iterationRange; // собственно диапазон строк или граф для подстановки значений

    // все по текущему документу
    public $document;
    public $unit;
    public $period;
    public $form;
    public $table;

    public $currentIteration;
    public $currentForm; // ORM Model обрабатываемой формы
    public $currentTable; // ORM Model обрабатываемой таблицы
    public $currentNode; // текущий узел ParseTreeNode - для обработки

    public function __construct(ParseTree $root, Table $table)
    {
        $this->root = $root;
        $this->form = Form::find($table->form_id);
        $this->table = $table;
        $this->setArguments();
    }

    public function setArguments()  { }

    public function prepareReadable()  { }

    public function exec(Document $document)  { }

    public function writeReadableCellAdresses(ParseTree $expression)
    {
        $expession_elements = [];
        foreach($expression->children as $element) {
            switch ($element->rule) {
                case  'celladress' :
                    $expession_elements = array_merge( $expession_elements, $this->rewriteCodes($element->tokens));
                    break;
                case 'operator' :
                case 'number' :
                    $expession_elements[] = $this->pad . $element->tokens[0]->text . $this->pad;
                    break;
                case 'summfunction' :
                    $expession_elements[] = 'сумма';
                    $expession_elements[] = '(';
                    $expession_elements = array_merge(
                        $expession_elements,
                        $this->rewriteCodes($element->children[0]->children[0]->tokens),
                        [' по '],
                        $this->rewriteCodes($element->children[0]->children[1]->tokens));
                    $expession_elements[] = ')';
                    break;
            }
        }
        return $expession_elements;
    }

    public function rewriteCodes($elements)
    {
        $expession_elements = [];
        $form_code = mb_substr($elements[0]->text, 1);

        if ( $this->form->form_code !==  $form_code && !empty($form_code)) {
            $expession_elements[] = 'ф.' . $form_code . $this->pad;
        }
        $table_code = mb_substr($elements[1]->text, 1);
        if ( $this->table->table_code !==  $table_code && !empty($table_code)) {
            $expession_elements[] = 'т.' . $table_code . $this->pad;
        }
        $row_code = mb_substr($elements[2]->text, 1);
        if ( $row_code) {
            $expession_elements[] = 'с.' . $row_code . $this->pad;
        }
        $column_code = mb_substr($elements[3]->text, 1);
        if ( $column_code) {
            $expession_elements[] = 'г.' . $column_code;
        }
        return $expession_elements;
    }

    public function setIterationRange(array $iteration_tokens)
    {
        if ($iteration_tokens[0]->text == '*') { // итерация по всем строкам или графам
            if ($this->iterationMode == 1) {
                $this->iterationRange = Row::OfTable($this->table->id)->where('deleted', 0)->pluck('row_code')->toArray();
            } elseif ($this->iterationMode == 2) {
                $this->iterationRange = Column::OfTable($this->table->id)->OfDataType()->where('deleted', 0)->pluck('column_index')->toArray();
            }
        } else { // подразумевается, что приведено перечисление строк или граф по которым нужно переписать неполные ссылки
            foreach ($iteration_tokens as $iteration_token) {
                if ($iteration_token->type == ControlFunctionLexer::NUMBER) {
                    $this->iterationRange[] = $iteration_token->text;
                }
            }
        }
    }

    public function fillIncompleteLinks($expression, $link)
    {
        $token_index = $this->iterationMode == 1 ? 2 : 3;
        $prefix = $this->iterationMode == 1 ? 'С' : 'Г';
        foreach($expression->children as $element) {
            if ($element->rule == 'celladress' && empty(mb_substr($element->tokens[$token_index]->text, 1))) {
                $element->tokens[$token_index]->text = $prefix . $link;
            }
        }
        return $expression;
    }

    public function calculate(ParseTree $expression)
    {
        //dd($expression);
        $eval_stack = [];
        foreach($expression->children as $element) {
            if ($element->rule == 'operator' || $element->rule == 'number' ) {
                $eval_stack[] = $element->tokens[0]->text;
            }
        }
        $inline = implode('', $eval_stack). ';';
        //var_dump($expression);
        $result = eval('return ' . $inline);
        return $result;
    }

    protected function chekoutRule($lp, $rp, $boolean)
    {
        $delta = 0.0001;
        // Если обе части выражения равны нулю - пропускаем проверку.
        if ($lp == 0 && $rp == 0) {
            return true;
        }
        switch ($boolean) {
            case '=' :
                $result = abs($lp - $rp) < $delta ? true : false;
                break;
            case '>' :
                $result = $lp > $rp;
                break;
            case '>=' :
                $result = $lp >= $rp;
                break;
            case '<' :
                $result = $lp < $rp;
                break;
            case '<=' :
                $result = $lp <= $rp;
                break;
            case '^' :
                $result = ($lp && $rp) || (!$lp && !$rp);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    public function rewrite_summfunctions(ParseTree $expression)
    {
        $this->currentNode = $expression;
        $elementcount = count($expression->children);
        $summfunction_ids = [];
        for ($i = 0; $i < $elementcount; $i++) {
            $element = $expression->children[$i];
            if ($element->rule == 'summfunction') {
                if (isset($expression->children[$i-1])) {
                    $operator = $expression->children[$i-1]->tokens[0]->text;
                } else {
                    $operator = '+';
                }
                $this->reduce_summfunction($element, $operator);
                $summfunction_ids[] = $i;
            }
        }

        // После редуцирования найденных функций удаляем выбранные узлы и предыдущий по отношению к ним оператор
        foreach($summfunction_ids as $id) {
            unset($expression->children[$id]);
            unset($expression->children[$id-1]);
        }
    }

    public function rewrite_celladresses(ParseTree $expression)
    {
        $this->currentNode = $expression;
        foreach($expression->children as $element) {
            if ($element->rule == 'celladress') {
                $this->reduce_celladress($element);
            }
        }
    }

    public function reduce_summfunction(ParseTree $sf, $operator)
    {
        $incomplete_row_adresses = false;
        $incomplete_column_adresses = false;
        $rows = [];
        $columns = [];

        $left_upper_corner_row = mb_substr($sf->children[0]->children[0]->tokens[2]->text, 1);
        if (!$left_upper_corner_row)  $incomplete_row_adresses = true;

        $left_upper_corner_column = mb_substr($sf->children[0]->children[0]->tokens[3]->text, 1);
        if ( !$left_upper_corner_column) $incomplete_column_adresses = true;

        $right_down_corner_row = mb_substr($sf->children[0]->children[1]->tokens[2]->text, 1);
        if ( !$right_down_corner_row) $incomplete_row_adresses = true;

        $right_down_corner_column = mb_substr($sf->children[0]->children[1]->tokens[3]->text, 1);
        if ( !$right_down_corner_column) $incomplete_column_adresses = true;

        // Проверка на неполные ссылки.
        if ($incomplete_row_adresses && $incomplete_column_adresses)  {
            throw new \Exception("Указан неправильный диапазон в функции 'сумма'. Допускаются неполные ссылки либо по строкам, либо по графам, но не одновременно");
        }
        if (!$incomplete_row_adresses) {
            $rows = $this->row_codes($left_upper_corner_row, $right_down_corner_row)->toArray();
        }

        if (!$incomplete_column_adresses) {
            $i = (int)$left_upper_corner_column;
            while($i <= $right_down_corner_column) {
                $columns[] = $i++;
            }
        }
        $cell_adresses = $this->inflate_matrix($rows, $columns);
        foreach($cell_adresses as $cell_adress) {
            $plus = new ControlFunctionParseTree('operator');
            $plus->addToken(new Token(ControlFunctionLexer::OPERATOR, $operator));
            $cell = new ControlFunctionParseTree('celladress');
            $cell->addToken(new Token(ControlFunctionLexer::FORMADRESS, $cell_adress[0]));
            $cell->addToken(new Token(ControlFunctionLexer::TABLEADRESS, $cell_adress[1]));
            $cell->addToken(new Token(ControlFunctionLexer::ROWADRESS, $cell_adress[2]));
            $cell->addToken(new Token(ControlFunctionLexer::COLUMNADRESS, $cell_adress[3]));
            $this->currentNode->addChild($plus);
            $this->currentNode->addChild($cell);
        }
        //dd($cell_adresses);
    }

    public function reduce_celladress(ParseTree $celladress)
    {
        $form_code = mb_substr($celladress->tokens[0]->text, 1);
        $table_code = mb_substr($celladress->tokens[1]->text, 1);
        // Проверяем относится ли редуцируемая ячейка к текущей таблице
        if ($this->form->form_code == $form_code || empty($form_code)) {
            $doc_id = $this->document->id;
            $f_id = $this->form->id;
        } else {
            $form = Form::OfCode($form_code)->first();
            if (is_null($form)) {
                throw new \Exception("Форма " . $form_code . " не существует");
            }

            $document = Document::OfUPF($this->document->ou_id, $this->document->period_id, $form->id)->first();
            if (is_null($document)) {
                $celladress->rule = 'number';
                $celladress->tokens = [];
                $celladress->addToken(new Token(ControlFunctionLexer::NUMBER, 0));
                $this->results['iterations'][$this->currentIteration]['documents_absent'] =  ['ou_id' => $this->document->ou_id, 'period_id' => $this->document->period_id, 'form_id' => $form->id];
                return $celladress;
            }
            $doc_id = $document->id;
            $f_id = $form->id;
            //dd($form->id);
            //dd($this->document->period_id);
            //dd($document);
        }
        if ($this->table->table_code == $table_code || empty($table_code)) {
            $t_id = $this->table->id;
        } else {
            $table = Table::OfFormTableCode($f_id, $table_code)->first();
            if (is_null($table)) {
                throw new \Exception("Таблицы " . $table_code . " нет в составе формы " . $form_code );
            }
            $t_id = $table->id;
        }
        $row_code = mb_substr($celladress->tokens[2]->text, 1);
        if (!$row_code) {
            throw new \Exception("Неполная ссылка (при отстутствии функции итерации по строкам). Не указан код строки. Получить значение ячейки невозможно");
        }
        $column_index = mb_substr($celladress->tokens[3]->text, 1);
        if (!$column_index) {
            throw new \Exception("Не указан индекс графы (при отстутствии функции итерации по графам). Получить значение ячейки невозможно");
        }

        $row = Row::ofTable($t_id)->where('row_code', $row_code)->first();
        //$row = Row::ofTable(10)->where('row_code', 69)->first();
        //dd($row);
        if (is_null($row)) {
            throw new \Exception("Строка с кодом " . $row_code . " не найдена в таблице " . $table->table_name . "(" . $table->table_code . ")");
        }
        //var_dump($this->form->form_code == $form_code || empty($form_code));
        //var_dump($f_id);
        //var_dump($this->form->form_code);
        //var_dump($form_code);
        //var_dump($this->table->table_code == $table_code || empty($table_code));
        //var_dump($t_id);
        //var_dump($column_index);
        //var_dump($this->table->table_code);
        //var_dump($table_code);
        $column = Column::ofTable($t_id)->where('column_index', $column_index)->first();
        //dd($column);
        if (is_null($column)) {
            //throw new \Exception("Графа с индексом " . $column_index . " не найдена в таблице " . $table->table_name . "(" . $table->table_code . ")");
            throw new \Exception("Графа с индексом " . $column_index . " не найдена в таблице " . $t_id);
        }
        $cell = Cell::ofDTRC($doc_id, $t_id, $row->id, $column->id)->first();
        //$cell = Cell::ofDTRC(7062, 10, 10,1362)->first();
        //dd($cell);
        is_null($cell) ? $value = 0 : $value = $cell->value;
        if (is_null($value)) $value = 0; // NULL значения трактуются как равные нулю
        $celladress->rule = 'number';
        $celladress->tokens = [];
        $celladress->addToken(new Token(ControlFunctionLexer::NUMBER, $value));
        return $celladress;
    }

    public function row_codes($start, $end)
    {
        $top = Row::ofTable($this->table->id)->where('row_code', $start)->first();
        $bottom = Row::ofTable($this->table->id)->where('row_code', $end)->first();
        $rows = Row::OfTable($this->table->id)->where('row_index', '>=', $top->row_index)->where('row_index', '<=', $bottom->row_index)->pluck('row_code');
        //dd($rows);
        return $rows;
    }

    public function inflate_matrix(array $rows = [], array $columns = [])
    {
        $matrix = [];
        $f = 'Ф' . $this->form->form_code;
        $t = 'Т' . $this->table->table_code;
        if (count($columns) === 0) { // Неполная ссылка по графам
            foreach($rows as $row) {
                $r = 'C' . $row;
                $matrix[] = [ $f, $t, $r , 'Г' ];
            }
        } elseif(count($rows) === 0) { // неполная ссылка по строкам
            foreach($columns as $column) {
                $c = 'Г' . $column;
                $matrix[] = [ $f, $t, 'С' , $c ];
            }
        } else {
            foreach($rows as $row) {
                $r = 'C' . $row;
                foreach($columns as $column) {
                    $c = 'Г' . $column;
                    $matrix[] = [ $f, $t, $r , $c ];
                }
            }
        }
        return $matrix;
    }

}
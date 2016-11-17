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
use App\UnitGroup;
use App\UnitGroupMember;

class ControlInterpreter
{
    public $root;
    public $errors = [];
    public $errorStack = [];
    public $unitScope = []; // область приложения функции (группы учреждений)
    public $readableFormula; // отображение формулы контроля в более удобочитаемом виде
    public $pad = ' ';
    public $results; // Протокол выполнения функции
    public $iterationMode; // режим перебора - без перебора(null) по строкам(1) и графам(2) при внутритабличном контроле
    public $iterationRange = []; // собственно диапазон строк или граф для подстановки значений

    // все по текущему документу
    public $document;
    public $unit;
    public $period;
    public $form;
    public $table;

    public $currentIteration;
    public $currentIterationLink;
    public $currentArgument;
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

    public function setUnitScope($group_alias)
    {
        if ($group_alias == '*') {
            return null;
        }
        $group = UnitGroup::OfSlug($group_alias)->first();
        if (!$group) {
            throw new \Exception("Группа медицинских организаций <" . $group_alias . "> не найдена");
        }
        return UnitGroupMember::OfGroup($group->id)->pluck('ou_id')->toArray();
    }

    public function inScope()
    {
        if (is_null($this->unitScope)) {
            return true;
        }
        if (!in_array($this->document->ou_id, $this->unitScope)) {
            return false;
        }
        return true;
    }

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
                case 'minmaxfunctions' :
                    $expession_elements[] = 'меньшее';
                    $expession_elements[] = '(';
                    $arguments = [];
                    foreach($element->children[0]->children as $arg) {
                        $arguments[]  = $this->rewriteCodes($arg->tokens);
                    }
                    $arg_text = [];
                    foreach($arguments as $arg) {
                        $arg_texts[] = implode('', $arg);
                    }

                    $expession_elements[] = implode(', ', $arg_texts);
                    $expession_elements[] = ')';
                    //dd($expession_elements);
                    break;
            }
        }
        return $expession_elements;
    }

    public function rewriteCodes($elements)
    {
        $expession_elements = [];
        $matches = $this->parseCelladress($celladress = $elements[0]->text);
        //dd($matches);
        $form_code = $matches['f'];
        $table_code = $matches['t'];
        $row_code = $matches['r'];
        $column_code = $matches['c'];

        /*$form_code = mb_substr($elements[0]->text, 1);
        $table_code = mb_substr($elements[1]->text, 1);
        $row_code = mb_substr($elements[2]->text, 1);
        $column_code = mb_substr($elements[3]->text, 1);*/

        if ( $this->form->form_code !==  $form_code && !empty($form_code)) {
            $expession_elements[] = 'ф.' . $form_code . $this->pad;
        }

        if ( $this->table->table_code !==  $table_code && !empty($table_code)) {
            $expession_elements[] = 'т.' . $table_code . $this->pad;
        }

        if ( $row_code) {
            $expession_elements[] = 'с.' . $row_code . $this->pad;
        }

        if ( $column_code) {
            $expession_elements[] = 'г.' . $column_code;
        }
        //dd($expession_elements);
        return $expession_elements;
    }

    protected function parseCelladress($celladress)
    {
        $correct = preg_match('/(?:Ф(?P<f>[\w.-]*))?(?:Т(?P<t>[\w.-]*))?(?:С(?P<r>[\w.-]*))?(?:Г(?P<c>\d{1,2}))?/', $celladress, $matches);
        if (!$correct) {
            throw new InterpreterException("Указан недопустимый адрес ячеейки " . $celladress);
        }
        if (!isset($matches['c'])) {
            $matches['c'] = '';
        }
        return $matches;
    }

    public function setIterationRange(array $iteration_nodes)
    {
        if ($iteration_nodes[0]->rule == 'all_rc') { // итерация по всем строкам или графам
            if ($this->iterationMode == 1) {
                $this->iterationRange = Row::OfTable($this->table->id)->where('deleted', 0)->pluck('row_code')->toArray();
            } elseif ($this->iterationMode == 2) {
                $this->iterationRange = Column::OfTable($this->table->id)->OfDataType()->where('deleted', 0)->pluck('column_index')->toArray();
            }
        } else { // подразумевается, что приведено перечисление строк или граф по которым нужно переписать неполные ссылки
            foreach ($iteration_nodes as $node) {
                if ($node->rule == 'iteration_number') {
                    //dd($node);
                    $this->iterationRange[] = $node->tokens[0]->text;
                } elseif($node->rule == 'iteration_range') {
                    $start = $node->tokens[0]->text;
                    $end = $node->tokens[2]->text;
                    if ($this->iterationMode == 1) {
                        $codes = $this->row_codes($start, $end)->toArray();
                        //dd($codes);
                    } elseif ($this->iterationMode == 2) {
                        $i = (int)$start;
                        while($i <= $end) {
                            $codes[] = $i++;
                        }
                    }
                    $this->iterationRange = array_merge($this->iterationRange, $codes );
                }
            }
        }
        //dd($this->iterationRange);
    }

    public function fillIncompleteLinks($expression)
    {
       if (isset($expression->children)) {
           foreach($expression->children as $element) {
               //dd($element);
               if ($element->rule == 'celladress') {
                   $element->tokens[0]->text = $this->completeAdress($element);
                   /*if ($element->rule == 'celladress' && empty(mb_substr($element->tokens[$token_index]->text, 1))) {
                       $element->tokens[$token_index]->text = $prefix . $link;
                   }*/
               }
               if (count($element->children > 0)) {
                   //dd(count($element->children));
                   $this->fillIncompleteLinks($element);
               }
           }
       }
        //dd($expression);
        return $expression;
    }

    protected function completeAdress($celladressNode)
    {
        $f = $this->form->form_code;
        $t = $this->table->table_code;
        $celladress = $celladressNode->tokens[0]->text;
        $matches = $this->parseCelladress($celladress);
        //dd($matches);
        if (!$matches['f']) {
            $matches['f'] = $f;
        }
        if (!$matches['t']) {
            $matches['t'] = $t;
        }
        switch (true) {
            case !$matches['r'] && $this->iterationMode == 1 :
                $matches['r'] = $this->currentIterationLink;
                break;
            case !$matches['c'] && $this->iterationMode == 2 :
                $matches['c'] = $this->currentIterationLink;
                break;
            case !$matches['r'] && $this->iterationMode == 2 :
                throw new InterpreterException("Неполная ссылка по строке при режиме итерации по графам по ячейке " . $celladress);
                break;
            case !$matches['c'] && $this->iterationMode == 1 :
                throw new InterpreterException("Неполная ссылка по графе при режиме итерации по строкам по ячейке " . $celladress);
                break;
            case (!$matches['r'] || !$matches['c']) && $this->iterationMode == null :
                throw new InterpreterException("Неполная ссылка при отсутствии режима итерации. Адрес ячейки " . $celladress);
                break;
        }

        $celladress = 'Ф'. $matches['f'] . 'Т' . $matches['t'] . 'С'. $matches['r'] . 'Г' . $matches['c'];
        return $celladress;
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
        //dd($inline);
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
        //dd($this->currentNode);
    }

/*    public function rewrite_minmaxfunctions(ParseTree $expression)
    {

        foreach($expression->children as $element) {
            if ($element->rule == 'minmaxfunctions') {
                foreach($element->children[0]->children as $celladress) {
                    $celladress->tokens[0]->text = $this->completeAdress($celladress);
                }
            }
        }
    }*/

    public function reduce_minmaxfunctions(ParseTree $expression)
    {
        //dd($expression);
        $this->currentNode = $expression;
        //$elementcount = count($expression->children);
        $minmaxfunctions_ids = [];
        $valuenodes = [];
        //for ($i = 0; $i < $elementcount; $i++) {
        foreach ( $expression->children as $key => $element) {
            if ($element->rule == 'minmaxfunctions') {
                foreach($element->children[0]->children as $celladress) {
                    $valuenodes[] = $this->reduce_celladress($celladress);
                }
                $minmaxfunctions_ids[] = $key;
            }
        }
        // После редуцирования найденных функций удаляем выбранные узлы и предыдущий по отношению к ним оператор
        foreach($minmaxfunctions_ids as $id) {
            unset($expression->children[$id]);
        }

        $values = [];
        foreach ($valuenodes as $valuenode) {
            $values[] = (float)$valuenode->tokens[0]->text;
        }
        // TODO: дописать для выбора функции "большее". Пока только минимальное значение из масива ячеек
        if (count($values) > 0) {
            $minvalue = min($values);
            $newnode = new ControlFunctionParseTree('number');
            $newnode->addToken(new Token(ControlFunctionLexer::NUMBER, $minvalue));
            $expression->addChild($newnode);
        }
        //dd($expression);
    }

    public function rewrite_celladresses(ParseTree $expression)
    {
        $this->currentNode = $expression;
        foreach($expression->children as $element) {
            if ($element->rule == 'celladress') {
                try {
                    $this->reduce_celladress($element);
                }
                catch (InterpreterException $e) {
                    $this->errorStack[] = ['code' => $e->getErrorCode(), 'message' => $e->getMessage() ];
                }
            }
        }
        $this->currentArgument = null;
    }

    public function reduce_summfunction(ParseTree $sf, $operator)
    {
        $incomplete_row_adresses = false;
        $incomplete_column_adresses = false;
        $rows = [];
        $columns = [];


        //$left_upper_corner_row = mb_substr($sf->children[0]->children[0]->tokens[2]->text, 1);
        $left_upper_corner = $this->parseCelladress($sf->children[0]->children[0]->tokens[0]->text);

        $left_upper_corner_row = $left_upper_corner['r'];
        //dd($left_upper_corner_row);

        if (!$left_upper_corner_row)  $incomplete_row_adresses = true;

        //$left_upper_corner_column = mb_substr($sf->children[0]->children[0]->tokens[3]->text, 1);
        $left_upper_corner_column = $left_upper_corner['c'];
        if ( !$left_upper_corner_column) $incomplete_column_adresses = true;
        //dd($left_upper_corner_column);

        $right_down_corner = $this->parseCelladress($sf->children[0]->children[1]->tokens[0]->text);

        //$right_down_corner_row = mb_substr($sf->children[0]->children[1]->tokens[2]->text, 1);
        $right_down_corner_row = $right_down_corner['r'];
        //dd($right_down_corner_row);

        if ( !$right_down_corner_row) $incomplete_row_adresses = true;

        //$right_down_corner_column = mb_substr($sf->children[0]->children[1]->tokens[3]->text, 1);
        $right_down_corner_column = $right_down_corner['c'];
        //dd($right_down_corner_column);
        if ( !$right_down_corner_column) $incomplete_column_adresses = true;

        // Проверка на неполные ссылки.
        if ($incomplete_row_adresses && $incomplete_column_adresses)  {
            throw new InterpreterException("Указан неправильный диапазон в функции 'сумма'. Допускаются неполные ссылки либо по строкам, либо по графам, но не одновременно");
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
            $cell->addToken(new Token(ControlFunctionLexer::CELLADRESS, $cell_adress));

            /*$cell->addToken(new Token(ControlFunctionLexer::FORMADRESS, $cell_adress[0]));
            $cell->addToken(new Token(ControlFunctionLexer::TABLEADRESS, $cell_adress[1]));
            $cell->addToken(new Token(ControlFunctionLexer::ROWADRESS, $cell_adress[2]));
            $cell->addToken(new Token(ControlFunctionLexer::COLUMNADRESS, $cell_adress[3]));*/

            $this->currentNode->addChild($plus);
            $this->currentNode->addChild($cell);
        }
        //dd($this->currentNode);
    }

    public function reduce_celladress(ParseTree $celladress)
    {
        $parsed_adress = $this->parseCelladress($celladress->tokens[0]->text);
        if (empty($parsed_adress['f']) || empty($parsed_adress['t']) || empty($parsed_adress['c']) || empty($parsed_adress['c'] ) ) {
            throw new InterpreterException("На этом этапе интерпретации функции контроля не допускаются неполные ссылки. Адрес ячейки " . $celladress->tokens[0]->text);
        }
        // Проверяем относится ли редуцируемая ячейка к текущей таблице

        if ($this->form->form_code == $parsed_adress['f']) {
            $doc_id = $this->document->id;
            $form = $this->form;
            $f_id = $this->form->id;
        } else {
            $form = Form::OfCode($parsed_adress['f'])->first();
            if (is_null($form)) {
                throw new InterpreterException("Форма " . $parsed_adress['f'] . " не существует", 1001);
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
        }
        if ($this->table->table_code == $parsed_adress['t']) {
            $t_id = $this->table->id;
            $table = $this->table;
        } else {
            $table = Table::OfFormTableCode($f_id, $parsed_adress['t'])->first();
            if (is_null($table)) {
                throw new InterpreterException("Таблицы " . $parsed_adress['t'] . " нет в составе формы " . $parsed_adress['f'], 1002);
            }
            $t_id = $table->id;
        }

        $row = Row::ofTable($t_id)->where('row_code', $parsed_adress['r'])->first();
        if (is_null($row)) {
            throw new InterpreterException("Строка с кодом " . $parsed_adress['r'] . " не найдена в таблице (" . $table->table_code . ") \"" . $table->table_name . "\" в форме " . $form->form_code, 1005);
        }
        $column = Column::ofTable($t_id)->where('column_index', $parsed_adress['c'])->first();
        if (is_null($column)) {
            throw new InterpreterException("Графа с индексом " . $parsed_adress['c'] . " не найдена в таблице " . $table->table_code, 1006);
        }
        $cell = Cell::ofDTRC($doc_id, $t_id, $row->id, $column->id)->first();

        // Записываем только левую (или единственную) часть сравнения
        if($this->currentArgument == 1) {
            $this->results['iterations'][$this->currentIteration]['cells'][] = ['row' => $row->id, 'column' => $column->id ];
        }
        //$this->results['iterations'][$this->currentIteration]['cells'][] = ['row' => $row->id, 'column' => $column->id ];
        is_null($cell) ? $value = 0 : $value = $cell->value;
        if (is_null($value)) $value = 0; // NULL значения трактуются как равные нулю
        $celladress->rule = 'number';
        $celladress->tokens = [];
        $celladress->addToken(new Token(ControlFunctionLexer::NUMBER, $value));
        return $celladress;
    }

    /*public function reduce_celladress(ParseTree $celladress)
    {
        $form_code = mb_substr($celladress->tokens[0]->text, 1);
        $table_code = mb_substr($celladress->tokens[1]->text, 1);
        // Проверяем относится ли редуцируемая ячейка к текущей таблице
        if ($this->form->form_code == $form_code || empty($form_code)) {
            $doc_id = $this->document->id;
            $form = $this->form;
            $f_id = $this->form->id;
        } else {
            $form = Form::OfCode($form_code)->first();
            if (is_null($form)) {
                throw new InterpreterException("Форма " . $form_code . " не существует", 1001);
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
        }
        if ($this->table->table_code == $table_code || empty($table_code)) {
            $t_id = $this->table->id;
            $table = $this->table;
        } else {
            $table = Table::OfFormTableCode($f_id, $table_code)->first();
            if (is_null($table)) {
                throw new InterpreterException("Таблицы " . $table_code . " нет в составе формы " . $form_code, 1002);
            }
            $t_id = $table->id;
        }
        $row_code = mb_substr($celladress->tokens[2]->text, 1);
        if (!$row_code) {
            throw new InterpreterException("Неполная ссылка (при отстутствии функции итерации по строкам). Не указан код строки. Получить значение ячейки невозможно", 1003);
        }
        $column_index = mb_substr($celladress->tokens[3]->text, 1);
        if (!$column_index) {
            throw new InterpreterException("Не указан индекс графы (при отстутствии функции итерации по графам). Получить значение ячейки невозможно", 1004);
        }

        $row = Row::ofTable($t_id)->where('row_code', $row_code)->first();
        if (is_null($row)) {
            throw new InterpreterException("Строка с кодом " . $row_code . " не найдена в таблице (" . $table->table_code . ") \"" . $table->table_name . "\" в форме " . $form->form_code, 1005);
        }
        $column = Column::ofTable($t_id)->where('column_index', $column_index)->first();
        if (is_null($column)) {
            throw new InterpreterException("Графа с индексом " . $column_index . " не найдена в таблице " . $t_id, 1006);
        }
        $cell = Cell::ofDTRC($doc_id, $t_id, $row->id, $column->id)->first();

        // Записываем только левую (или единственную) часть сравнения
        if($this->currentArgument == 1) {
            $this->results['iterations'][$this->currentIteration]['cells'][] = ['row' => $row->id, 'column' => $column->id ];
        }
        //$this->results['iterations'][$this->currentIteration]['cells'][] = ['row' => $row->id, 'column' => $column->id ];
        is_null($cell) ? $value = 0 : $value = $cell->value;
        if (is_null($value)) $value = 0; // NULL значения трактуются как равные нулю
        $celladress->rule = 'number';
        $celladress->tokens = [];
        $celladress->addToken(new Token(ControlFunctionLexer::NUMBER, $value));
        return $celladress;
    }*/

    public function row_codes($start, $end)
    {
        //dd($this->table->id);
        $top = Row::ofTable($this->table->id)->where('row_code', $start)->first();
        if (!$top) {
            throw new InterpreterException("Задана несуществующая строка в начале диапазона в таблице " . $this->table->table_code, 1005);
        }
        $bottom = Row::ofTable($this->table->id)->where('row_code', $end)->first();
        if (!$bottom) {
            throw new InterpreterException("Задана несуществующая строка в конце диапазона в таблице " . $this->table->table_code, 1005);
        }
        $rows = Row::OfTable($this->table->id)->where('row_index', '>=', $top->row_index)->where('row_index', '<=', $bottom->row_index);
        if (!$rows) {
            throw new InterpreterException("Задан неправильный диапазон строк. Искомые строки не найдены в таблице " . $this->table->table_code, 1005);
        }
        //dd($rows);
        return $rows->pluck('row_code');
    }

    public function inflate_matrix(array $rows = [], array $columns = [], $f = null, $t = null)
    {
        // TODO: На этом этапе можно дополнить неполные ссылки
        $matrix = [];
        if (!$f) {
            $f = $this->form->form_code;
        }
        if (!$t) {
            $t = $this->table->table_code;
        }
        if (count($columns) === 0) { // Неполная ссылка по графам
            foreach($rows as $row) {
                //$matrix[] = [ $f, $t, $r , 'Г' ];
                $matrix[] = 'Ф'. $f . 'Т' . $t . 'С' . $row . 'Г' ;
            }
        } elseif(count($rows) === 0) { // неполная ссылка по строкам
            foreach($columns as $column) {
                //$matrix[] = [ $f, $t, 'С' , $c ];
                $matrix[] = 'Ф'. $f . 'Т' . $t . 'С' . 'Г' . $column;
            }
        } else {
            foreach($rows as $row) {
                foreach($columns as $column) {
                    //$matrix[] = [ $f, $t, $r , $c ];
                    $matrix[] = 'Ф'. $f . 'Т' . $t . 'С'. $row . 'Г' . $column;
                }
            }
        }
        return $matrix;
    }

}
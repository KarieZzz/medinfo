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
use App\Unit;
use App\UnitGroup;
use App\UnitGroupMember;

class ControlInterpreter
{
    public $root;
    public $functionIndex;
    public $errors = [];
    public $errorStack = [];
    public $unitScope = []; // область приложения функции (группы учреждений)
    public $allowedDocumentType;
    public $readableFormula; // отображение формулы контроля в более удобочитаемом виде
    //public $pad = '&thinsp;'; // тонкая шпация
    //public $pad = '&#8197;'; // четверть круглой
    //public $pad = '&#8202;'; // волосяная шпация
    //public $pad = '&ensp;'; // полукруглая шпация
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
    public $currentPeriod = '1'; // По умолчанию отчетный период проверяемого документа

    public function __construct(ParseTree $root, Table $table, $fIndex)
    {
        $this->root = $root;
        $this->form = Form::find($table->form_id);
        $this->table = $table;
        $this->functionIndex = $fIndex;
        $this->setArguments();
    }

    public function setArguments()  { }

    public function prepareReadable()  { }

    public function exec(Document $document)  { }

    public function setUnitScope(ParseTree $grouparray)
    {
        //dd($grouparray);
        $included_group = [];
        $excluded_group = [];
        $units_in_scope = [];
        $units_notin_scope = [];

        foreach ($grouparray->children as $element) {
            if ($element->rule == 'all') {
                return null;
            } elseif ($element->rule == 'includedgroup') {
                $included_group[] = $element->tokens[0]->text;
            } elseif ($element->rule == 'excludedgroup') {
                $excluded_group[] = $element->tokens[1]->text;
            }
        }

        //dd($excluded_group);

        foreach ($included_group as $groupalias) {
            $units_in_scope = array_merge($units_in_scope, $this->get_units($groupalias));
        }
        $units_in_scope = array_unique($units_in_scope);

        // Если нет включенных в контроль групп, убираем исключенные юниты из общего числа юнитов
        if (count($excluded_group) > 0 && count($included_group) == 0 ) {
            $units_in_scope = Unit::all()->pluck('id')->toArray(); // все юниты
            $units_in_scope = array_merge($units_in_scope, UnitGroup::all()->pluck('id')->toArray()); // плюс все группы, что бы не выпали из контроля
        }

        foreach ($excluded_group as $groupalias) {
            $units_notin_scope = array_merge($units_notin_scope, $this->get_units($groupalias));

        }
        $units_in_scope = array_diff($units_in_scope, $units_notin_scope); // отнимаем все исключаемые юниты;
        //dd($units_in_scope);
        return $units_in_scope;
    }

    public function get_static_scope()
    {

    }

    public function get_units($groupalias)
    {
        //dd($groupalias);
        $units = [];
        switch ($groupalias) {
            case 'сводные' :
                $this->allowedDocumentType = 2;
                break;
            case 'первичные' :
                $this->allowedDocumentType = 1;
                break;
            case 'оп' :
            case 'обособподр' :
                $units = Unit::SubLegal()->pluck('id')->toArray();
                break;
            case 'юл' :
            case 'юрлица' :
                $units = Unit::Legal()->pluck('id')->toArray();
                break;
            case 'тер' :
            case 'территории' :
                $units = Unit::Territory()->pluck('id')->toArray();
                break;
            default:
                $group = UnitGroup::OfSlug($groupalias)->first();
                if (!$group) {
                    throw new \Exception("Группа медицинских организаций <" . $groupalias . "> не найдена");
                }
                $units = UnitGroupMember::OfGroup($group->id)->pluck('ou_id')->toArray();
                break;
        }
        return $units;
    }

    public function inScope()
    {
        //dd($this->document->dtype);
        if (!is_null($this->allowedDocumentType) && $this->document->dtype !== $this->allowedDocumentType) {
            //dd($this->document->dtype);
            return false;
        } elseif (!is_null($this->allowedDocumentType) && $this->document->dtype == $this->allowedDocumentType) {
            return true;
        }

        if (is_null($this->unitScope)) {
            //dd($this->document->dtype);
            return true;
        }

        if (!in_array($this->document->ou_id, $this->unitScope)) {
            //dd($this->document->dtype);
            return false;
        }
        return true;
    }

    public function writeReadableCellAdresses(ParseTree $expression)
    {
        $expession_elements = [];
        foreach($expression->children as $element) {
            switch ($element->rule) {
                case 'celladress' :
                    $expession_elements = array_merge( $expession_elements, $this->rewriteCodes($element->tokens[0]));
                    break;
                case 'operator' :
                case 'number' :
                    $expession_elements[] = ' ' . $element->tokens[0]->text . ' ';
                    break;
                case 'summfunction' :
                    $cellarray = $element->children[0];
                    $func_name = 'сумма(';
                    $function_elements = $this->writeReadableCellArray($cellarray);
                    $expession_elements[] = $func_name . implode(', ', $function_elements) . ')';
                    break;
                case 'minmaxfunctions' :
                    $cellarray = $element->children[0];
                    $func_name = 'меньшее(';
                    $function_elements = $this->writeReadableCellArray($cellarray);
                    $expession_elements[] = $func_name . implode(', ', $function_elements) . ')';
                    break;
                case 'diapason' :
                    $cellarray = $element->children[0];
                    $func_name = 'диапазон(';
                    $function_elements = $this->writeReadableCellArray($cellarray);
                    $expession_elements[] = $func_name . implode(', ', $function_elements) . ')';
                    break;
            }
        }
        return $expession_elements;
    }

    protected function writeReadableCellArray(ParseTree $cellarray)
    {
        $function_elements = [];
        foreach($cellarray->children as $arrayelement) {
            if ($arrayelement->rule == 'cellrange') {
                //dd($arrayelement);
                $function_elements[] = implode($this->rewriteCodes($arrayelement->tokens[0])) . ' по ' . implode($this->rewriteCodes($arrayelement->tokens[2]));
            } elseif ($arrayelement->rule == 'celladress') {
                $function_elements[] = implode($this->rewriteCodes($arrayelement->tokens[0]));
            }
        }
        return $function_elements;
    }

    public function rewriteCodes($adress_token)
    {
        $expession_elements = [];
        $matches = ExpressionTranslater::parseCelladress($adress_token->text);
        //var_dump($matches);

        $form_code = $matches['f'];
        $table_code = $matches['t'];
        $row_code = $matches['r'];
        $column_code = $matches['c'];

        /*$form_code = mb_substr($elements[0]->text, 1);
        $table_code = mb_substr($elements[1]->text, 1);
        $row_code = mb_substr($elements[2]->text, 1);
        $column_code = mb_substr($elements[3]->text, 1);*/

        if ( $form_code ) {
            $expession_elements[] = 'ф.' . $form_code . $this->pad;
        }

        if ( $table_code ) {
            $expession_elements[] = 'т.' . $table_code . $this->pad;
        }

        if ( $row_code) {
            $expession_elements[] = 'с.' . $row_code . $this->pad;
        }

        if ( $column_code) {
            $expession_elements[] = 'г.' . $column_code;
        }

        if (isset($matches['p'])) {
            if ($matches['p'] === '0') {
                $expession_elements[] = ' (пред. период)';
            }
        }
        //dd($expession_elements);
        return $expession_elements;
    }

    public function setIterationRange(array $iteration_nodes)
    {
        if ($iteration_nodes[0]->rule == 'all') { // итерация по всем строкам или графам
            if ($this->iterationMode == 1) {
                $this->iterationRange = Row::OfTable($this->table->id)->where('deleted', 0)->pluck('row_code')->toArray();
            } elseif ($this->iterationMode == 2) {
                $this->iterationRange = Column::OfTable($this->table->id)->OfDataType()->where('deleted', 0)->pluck('column_index')->toArray();
                //dd($this->iterationRange);
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
                        $codes = ExpressionTranslater::row_codes($start, $end, $this->table);
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
        //dd($expression);
       if (!isset($expression->children)) { return $expression; }
       foreach($expression->children as $element) {

           if ($element->rule == 'celladress') {
               //dd($element);
               $this->completeAdress($element);
           }
           $this->fillIncompleteLinks($element);
       }
        return $expression;
    }

    protected function completeAdress($celladressNode)
    {
        //dd($celladressNode);
        $celladress = $celladressNode->tokens[0]->text;
        //dd($celladress );
        $matches = ExpressionTranslater::parseCelladress($celladress);
        //dd($matches);

        if (!$matches['f']) {
            $matches['f'] = $this->form->form_code;
        }
        if (!$matches['t']) {
            $matches['t'] = $this->table->table_code;
        }
        if ((!$matches['r'] || !$matches['c']) && $this->iterationMode == null) {
            throw new InterpreterException("Неполная ссылка на строку/графу при отсутствии режима итерации. Адрес ячейки " . $celladress);
        }
        //if (!isset($matches['p'])) {
        if (!isset($matches['p'])) {
            $matches['p'] = $this->currentPeriod;
        }
        $celladress = 'Ф'. $matches['f'] . 'Т' . $matches['t'] . 'С'. $matches['r'] . 'Г' . $matches['c'] . 'П' . $matches['p'];
        //dd($celladress);
        $celladressNode->tokens[0]->text = $celladress;
        return $celladress;
    }

    public function calculate(ParseTree $expression)
    {
        $eval_stack = [];
        foreach($expression->children as $element) {
            if ($element->rule == 'operator' || $element->rule == 'number' ) {
                $eval_stack[] = $element->tokens[0]->text;
            }
        }
        $inline = implode('', $eval_stack). ';';
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
                $cellarray = $element->children[0];
                //dd($cellarray);
                foreach($cellarray->children as $celladress) {
                    try {
                        $valuenodes[] = $this->reduce_celladress($celladress);
                    }
                    catch (InterpreterException $e) {
                        $this->errorStack[] = ['code' => $e->getErrorCode(), 'message' => $e->getMessage() ];
                    }
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
        // TODO: дописать для выбора функции "большее". Пока только минимальное значение из масcива ячеек
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
        //dd($this->currentNode);
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
        //dd($expression);
    }

    public function reduce_summfunction(ParseTree $sf, $operator)
    {
        $incomplete_row_adresses = false;
        $incomplete_column_adresses = false;
        $rows = [];
        $columns = [];
        //dd($sf);

        //$left_upper_corner_row = mb_substr($sf->children[0]->children[0]->tokens[2]->text, 1);
        $left_upper_corner = ExpressionTranslater::parseCelladress($sf->children[0]->children[0]->tokens[0]->text);

        $left_upper_corner_row = $left_upper_corner['r'];
        //dd($left_upper_corner_row);

        if (!$left_upper_corner_row)  $incomplete_row_adresses = true;

        //$left_upper_corner_column = mb_substr($sf->children[0]->children[0]->tokens[3]->text, 1);
        $left_upper_corner_column = $left_upper_corner['c'];
        if ( !$left_upper_corner_column) $incomplete_column_adresses = true;
        //dd($left_upper_corner_column);

        $right_down_corner = ExpressionTranslater::parseCelladress($sf->children[0]->children[1]->tokens[0]->text);

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
            $rows = ExpressionTranslater::row_codes($left_upper_corner_row, $right_down_corner_row, $this->table);
        }

        if (!$incomplete_column_adresses) {
            $i = (int)$left_upper_corner_column;
            while($i <= $right_down_corner_column) {
                $columns[] = $i++;
            }
        }
        $cell_adresses = ExpressionTranslater::inflate_matrix($rows, $columns, null, null, $this->currentPeriod);
        //dd($cell_adresses);
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
        //dd($celladress);
        $parsed_adress = ExpressionTranslater::parseCelladress($celladress->tokens[0]->text);

        if ( empty($parsed_adress['f']) || empty($parsed_adress['t']) || empty($parsed_adress['c']) || empty($parsed_adress['c']) &&
            ($parsed_adress['p'] !== '0' ||  $parsed_adress['p'] !== '1')) {
            throw new InterpreterException("На этом этапе интерпретации функции контроля не допускаются неполные ссылки. Адрес ячейки " . $celladress->tokens[0]->text);
        }
        //dd($parsed_adress);
        //var_dump($parsed_adress);
        // Проверяем в текущем периоде находится редуцируемая ячейка?
        // Текущий период

        if ($parsed_adress['p'] === '1') {
            // Проверяем относится ли редуцируемая ячейка к текущей таблице
            //dd($parsed_adress);
            if ($this->form->form_code == $parsed_adress['f']) {
                $doc_id = $this->document->id;
                $form = $this->form;
                $f_id = $this->form->id;
            } else {
                $form = Form::OfCode($parsed_adress['f'])->first();
                //dd($form);
                if (is_null($form)) {
                    ExpressionTranslater::numberizeCelladress($celladress);
                    throw new InterpreterException("Форма " . $parsed_adress['f'] . " не существует", 1001);
                }
                $document = Document::OfTUPF($this->document->dtype, $this->document->ou_id, $this->document->period_id, $form->id)->first();
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
        } elseif($parsed_adress['p'] === '0') {


            if ($this->form->form_code == $parsed_adress['f']) {
                $form = $this->form;
            } else {
                $form = Form::OfCode($parsed_adress['f'])->first();
                //dd($form);
                if (is_null($form)) {
                    ExpressionTranslater::numberizeCelladress($celladress);
                    throw new InterpreterException("Форма " . $parsed_adress['f'] . " не существует", 1001);
                }
            }

            $current_period = Period::find($this->document->period_id);
            $previous_period = Period::LastYear($current_period->begin_date->year)->first();
            $previous_document = Document::OfTUPF($this->document->dtype, $this->document->ou_id, $previous_period->id, $this->document->form_id)->first();
            if (is_null($previous_document)) {
                $celladress->rule = 'number';
                $celladress->tokens = [];
                $celladress->addToken(new Token(ControlFunctionLexer::NUMBER, 0));
                $this->results['iterations'][$this->currentIteration]['documents_absent'] =  ['ou_id' => $this->document->ou_id, 'period_id' => $this->document->period_id, 'form_id' => $form->id];
                return $celladress;
            }
            $doc_id = $previous_document->id;
            $f_id = $form->id;
        }



        //if ($this->table->table_code == $parsed_adress['t']) {
          //  $t_id = $this->table->id;
            //$table = $this->table;
        //} else {
        $table = Table::OfFormTableCode($f_id, $parsed_adress['t'])->first();
        if (is_null($table)) {
            ExpressionTranslater::numberizeCelladress($celladress);
            throw new InterpreterException("Таблицы " . $parsed_adress['t'] . " нет в составе формы " . $parsed_adress['f'], 1002);
        }
        $t_id = $table->id;
        //}
        //dd($table->form);
        $row = Row::ofTable($t_id)->where('row_code', $parsed_adress['r'])->first();
        if (is_null($row)) {
            ExpressionTranslater::numberizeCelladress($celladress);
            throw new InterpreterException("Строка с кодом " . $parsed_adress['r'] . " не найдена в таблице (" . $table->table_code . ") \"" . $table->table_name
                . "\" в форме " . $form->form_code, 1005);
        }
        $column = Column::ofTable($t_id)->where('column_index', $parsed_adress['c'])->first();

        if (is_null($column)) {
            ExpressionTranslater::numberizeCelladress($celladress);
            throw new InterpreterException("Графа с индексом " . $parsed_adress['c'] . " не найдена в таблице " . $table->table_code . ") \"" . $table->table_name
                . "\" в форме " . $this->form->form_code, 1006);
        }
        $cell = Cell::ofDTRC($doc_id, $t_id, $row->id, $column->id)->first();

        // Записываем только левую (или единственную) часть сравнения
        if($this->currentArgument == 1) {
            $this->results['iterations'][$this->currentIteration]['cells'][] = ['row' => $row->id, 'column' => $column->id ];
        }
        //$this->results['iterations'][$this->currentIteration]['cells'][] = ['row' => $row->id, 'column' => $column->id ];
        is_null($cell) ? $value = 0 : $value = $cell->value;
        return ExpressionTranslater::numberizeCelladress($celladress, $value);
    }

}
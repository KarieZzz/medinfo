<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 18.11.2016
 * Time: 9:54
 */

namespace App\Medinfo\Lexer;

use App\Form;
use App\Table;
use App\Row;


class ExpressionTranslater
{

    public $form;
    public $table;
    public $period;
    public $expression;
    public $nodeStack = [];

    public function __construct(Form $form, Table $table, int $period = 1)
    {
        $this->form = $form;
        $this->table = $table;
        $this->period = $period;
    }

    public function translate(ParseTree $expression)
    {
        $this->expression = $expression;
        $this->nodeStack = [];
        $this->translateCellRanges($this->expression);
        $this->translateSummfunction($this->expression);

        return $this->expression;
    }

    private function translateCellRanges($currentNode)
    {
        if (!isset($currentNode->children)) { return $currentNode; }
        foreach($currentNode->children as $key => $element) {
            if ($element->rule == 'cellrange') {
                $this->reduce_cellrange($element, $currentNode);
                unset($currentNode->children[$key]);
            }
            $this->translateCellRanges($element);
        }
        return $currentNode;
    }

    private function translateSummfunction($currentNode) {
        if (!isset($currentNode->children)) { return $currentNode; }
        foreach($currentNode->children as $key => $element) {
            if ($element->rule == 'summfunction') {
                unset($currentNode->children[$key]);
                //dd($currentNode);
                //dd($key);
                //dd($currentNode->children[$key-1]);
                if (isset($currentNode->children[$key-1])) {
                    $operator = $currentNode->children[$key-1]->tokens[0]->text;
                    unset($currentNode->children[$key-1]);
                } else {
                    $operator = '+';
                }
                //dd($operator);
                $this->reduce_summfunction($element, $operator, $currentNode);

            }
            $this->translateCellRanges($element);
        }
        return $currentNode;
    }

    private function reduce_cellrange(ParseTree $cellrange, $parent)
    {
        $incomplete_row_adresses = false;
        $incomplete_column_adresses = false;
        $rows = [];
        $columns = [];

        $left_upper_corner = self::parseCelladress($cellrange->tokens[0]->text);
        if (! $left_upper_corner['r'])  $incomplete_row_adresses = true;
        if ( ! $left_upper_corner['c']) $incomplete_column_adresses = true;


        $right_down_corner = self::parseCelladress($cellrange->tokens[2]->text);
        if ( !$right_down_corner['r']) $incomplete_row_adresses = true;
        if ( ! $right_down_corner['c']) $incomplete_column_adresses = true;


        if (($left_upper_corner['f'] == $this->form->form_code) || !$left_upper_corner['f']) {
            $form = $this->form;
        } else {
            $form = Form::OfCode($left_upper_corner['f'])->first();
        }

        if (($left_upper_corner['t'] == $this->table->table_code) || !$left_upper_corner['t']) {
            $table = $this->table;
        } else {
            $table = Table::OfFormTableCode($form->id, $left_upper_corner['t'])->first();
        }

        // Предыдущий период проверим по верхнему левому углу
        if ( isset($left_upper_corner['p'])) {
            $left_upper_corner['p'] === '0' ? $this->period = '0' : $this->period = '1';
        }

        // Проверка на неполные ссылки.
        if ($incomplete_row_adresses && $incomplete_column_adresses)  {
            throw new InterpreterException("Указан неправильный диапазон в функции 'сумма'. Допускаются неполные ссылки либо по строкам, либо по графам, но не одновременно");
        }
        if (!$incomplete_row_adresses) {
            $rows = self::row_codes( $left_upper_corner['r'], $right_down_corner['r'], $table);
        }

        if (!$incomplete_column_adresses) {
            $i = (int) $left_upper_corner['c'];
            while($i <=  $right_down_corner['c']) {
                $columns[] = $i++;
            }
        }
        $cell_adresses = self::inflate_matrix($rows, $columns, $form->form_code, $table->table_code, $this->period);
        //dd($cell_adresses );
        foreach($cell_adresses as $cell_adress) {
            $cell = new ControlFunctionParseTree('celladress');
            $cell->addToken(new Token(ControlFunctionLexer::CELLADRESS, $cell_adress['string']));
            //$cell->addToken(new Token(ControlFunctionLexer::FORMADRESS, $cell_adress['f']));
            //$cell->addToken(new Token(ControlFunctionLexer::TABLEADRESS, $cell_adress['t']));
            //$cell->addToken(new Token(ControlFunctionLexer::ROWADRESS, $cell_adress['r']));
            //$cell->addToken(new Token(ControlFunctionLexer::COLUMNADRESS, $cell_adress['c']));
            $parent->addChild($cell);
        }
        //dd($this->expression);
    }

    private function reduce_summfunction($element, $operator, $currentNode)
    {
        $cellarray = $element->children[0];

        foreach ($cellarray->children as $celladress) {
            $operatorNode = new ControlFunctionParseTree('operator');
            $operatorNode->addToken(new Token(ControlFunctionLexer::OPERATOR, $operator));
            $currentNode->children[] = $operatorNode;
            $currentNode->children[] = $celladress;
        }
    }

    public function unsetStacked()
    {
        foreach ($this->nodeStack as $node) {
            unset($node);
        }
    }

    public static function parseCelladress($celladress)
    {
        //dd($celladress);
        //$correct = preg_match('/(?:Ф(?P<f>[\w.-]*))?(?:Т(?P<t>[\w.-]*))?(?:С(?P<r>[\w.-]*))?(?:Г(?P<c>\d{1,2}))?/u', $celladress, $matches);
        $correct = preg_match('/(?:Ф(?P<f>[а-я0-9.-]*))?(?:Т(?P<t>[а-я0-9.-]*))?(?:С(?P<r>[0-9.-]*))?(?:Г(?P<c>\d{1,2}))?(?:П(?P<p>[01]))?/u', $celladress, $matches);
        if (!$correct) {
            throw new InterpreterException("Указан недопустимый адрес ячейки " . $celladress);
        }
        if (!isset($matches['r'])) {
            $matches['r'] = '';
        }
        if (!isset($matches['c'])) {
            $matches['c'] = '';
        }
/*        if (!isset($matches['p'])) {
            $matches['p'] = '';
        }*/
        //dd($matches);
        return $matches;
    }

    public static function row_codes($start, $end, Table $table)
    {
        $top = Row::ofTable($table->id)->where('row_code', $start)->first();
        if (!$top) {
            throw new InterpreterException("Задана несуществующая строка в начале диапазона в таблице " . $table->table_code, 1005);
        }
        $bottom = Row::ofTable($table->id)->where('row_code', $end)->first();
        if (!$bottom) {
            throw new InterpreterException("Задана несуществующая строка в конце диапазона в таблице " . $table->table_code, 1005);
        }
        $rows = Row::OfTable($table->id)->where('row_index', '>=', $top->row_index)->where('row_index', '<=', $bottom->row_index);
        if (!$rows) {
            throw new InterpreterException("Задан неправильный диапазон строк. Искомые строки не найдены в таблице " . $table->table_code, 1005);
        }

        return $rows->pluck('row_code')->toArray();
    }

    public static function inflate_matrix(array $rows, array $columns, $form = null, $table = null, $period)
    {
        $matrix = [];
            if (count($columns) === 0) { // Неполная ссылка по графам
            foreach($rows as $row) {
                //$matrix[] = [ $f, $t, $r , 'Г' ];
                $matrix[] = [ 'string' => 'Ф'. $form . 'Т' . $table . 'С' . $row . 'Г' . 'П' . $period,
                'f' => $form, 't' => $table, 'r' => $row , 'c' => '', 'p' => $period] ;
            }
        } elseif(count($rows) === 0) { // неполная ссылка по строкам
            foreach($columns as $column) {
                //$matrix[] = [ $f, $t, 'С' , $c ];
                $matrix[] = [ 'string' => 'Ф'. $form . 'Т' . $table . 'С' . 'Г' . $column . 'П' . $period ,
                    'f' => $form, 't' => $table, 'r' => '' , 'c' =>  $column, 'p' => $period ];
            }
        } else {
            foreach($rows as $row) {
                foreach($columns as $column) {
                    //$matrix[] = [ $f, $t, $r , $c ];
                    $matrix[] = [ 'string' => 'Ф'. $form . 'Т' . $table . 'С'. $row . 'Г' . $column . 'П' . $period,
                        'f' => $form, 't' => $table, 'r' => $row , 'c' =>  $column , 'p' => $period];
                }
            }
        }
        return $matrix;
    }

    public static function numberizeCelladress(ParseTree $celladress, $value = null)
    {
        if (is_null($value)) $value = 0; // NULL значения трактуются как равные нулю
        $celladress->rule = 'number';
        $celladress->tokens = [];
        $celladress->addToken(new Token(ControlFunctionLexer::NUMBER, $value));
        return $celladress;
    }



}
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

class CompareControlInterpreter
{
    public $root;
    public $lpExpression; // node левая часть сравнения
    public $rpExpression; // node правая часть сравнения
    public $boolean; // знак сравнения в контроле
    public $unitScope; // область приложения функции (группы учреждений)
    public $iterationMode; // режим перебора - без перебора(null) по строкам(1) и графам(2) при внутритабличном контроле
    public $iterationRange; // собственно диапазон строк или граф для подстановки значений

    // все по таблице текущего документа
    public $document;
    public $unit;
    public $period;
    public $form;
    public $table;

    public $currentForm; // ORM Model обрабатываемой формы
    public $currentTable; // ORM Model обрабатываемой таблицы
    public $currentNode; // текущий узел ParseTreeNode - для обработки

    public function __construct(ParseTree $root, int $form, int $table)
    {
        $this->root = $root;
        $this->form = Form::find($form);
        $this->table = Table::find($table);
        $this->setArguments();
    }

    public function setArguments()
    {
        $this->lpExpression = $this->root->children[0];
        $this->rpExpression = $this->root->children[1];
        $this->boolean = $this->root->children[2]->tokens[0]->text;
        if (isset($this->root->children[3]->children[0]->tokens[0])) {
            $this->unitScope = $this->root->children[3]->children[0]->tokens[0]->text;
        }
        if (count($this->root->children[4]->children[0]->tokens)) {
            $this->iterationRange = $this->root->children[4]->children[0]->tokens[0]->text;
        }
        $this->iterationMode = $this->root->children[4]->tokens[0]->text == 'строки' ? 1 : 2;
    }

    public function exec(Document $document)
    {
        $this->document = $document;
        $this->rewrite_summfunctions($this->lpExpression);
        $this->rewrite_summfunctions($this->rpExpression);
        $this->rewrite_celladresses($this->lpExpression);
        $this->rewrite_celladresses($this->rpExpression);
        dd($this->rpExpression);
    }

    public function rewrite_summfunctions($expression)
    {
        $this->currentNode = $expression;
        $elementcount = count($expression->children);
        $summfunction_ids = [];
        for ($i = 0; $i < $elementcount; $i++) {
            $element = $expression->children[$i];
            if ($element->rule == 'summfunction') {
                $this->reduce_summfunction($element);
                $summfunction_ids[] = $i;
            }
        }

        // После редуцирования найденных функций удаляем выбранные узлы и предыдущий по отношению к ним оператор
        foreach($summfunction_ids as $id) {
            unset($expression->children[$id]);
            unset($expression->children[$id-1]);
        }
    }

    public function rewrite_celladresses($expression)
    {
        $this->currentNode = $expression;
        //$elementcount = count($expression->children);
        //$celladress_ids = [];
        //for ($i = 0; $i < $elementcount; $i++) {
        foreach($expression->children as $element) {
            if ($element->rule == 'celladress') {
                $this->reduce_celladress($element);
                //$celladress_ids[] = $i;
            }
        }
        // После редуцирования найденных функций удаляем выбранные узлы и предыдущий по отношению к ним оператор
        //foreach($celladress_ids as $id) {
        //  unset($expression->children[$id]);
        //unset($expression->children[$id-1]);
        //}
    }

    public function reduce_summfunction(ParseTree $sf)
    {
        $form = Form::ofCode(mb_substr($sf->children[0]->children[0]->tokens[0]->text, 1))->first();
        $table = Table::ofForm($form->id)->where('table_code', mb_substr($sf->children[0]->children[0]->tokens[1]->text, 1))->first();
        $this->currentTable = $table;
        $this->currentForm = $form;
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
            $plus->addToken(new Token(ControlFunctionLexer::OPERATOR, '+'));
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
        $row_code = mb_substr($celladress->tokens[2]->text, 1);
        $column_index = mb_substr($celladress->tokens[3]->text, 1);

        $row = Row::ofTable($this->table->id)->where('row_code', $row_code)->first();
        $column = Column::ofTable($this->table->id)->where('column_index', $column_index)->first();
        $cell = Cell::ofDTRC($this->document->id, $this->table->id, $row->id, $column->id)->first();
        //$newcellnode = new ControlFunctionParseTree('number');
        $value = $cell ? $cell->value : 0;
        $celladress->rule = 'number';
        $celladress->tokens = [];
        $celladress->addToken(new Token(ControlFunctionLexer::NUMBER, $value));
        return $celladress;
    }

    public function row_codes($start, $end)
    {
        $top = Row::ofTable($this->currentTable->id)->where('row_code', $start)->first();
        $bottom = Row::ofTable($this->currentTable->id)->where('row_code', $end)->first();
        $rows = Row::OfTable($this->currentTable->id)->where('row_index', '>=', $top->row_index)->where('row_index', '<=', $bottom->row_index)->pluck('row_code');
        //dd($rows);
        return $rows;
    }

    public function inflate_matrix(array $rows = [], array $columns = [])
    {
        $matrix = [];
        $f = 'Ф' . $this->currentForm->form_code;
        $t = 'Т' . $this->currentTable->table_code;
        if (count($columns) == 0) { // Неполная ссылка по графам
            foreach($rows as $row) {
                $r = 'C' . $row;
                $matrix[] = [ $f, $t, $r , 'Г' ];
            }
        } elseif(count($rows) == 0) { // неполная ссылка по строкам
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
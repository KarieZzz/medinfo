<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2016
 * Time: 9:38
 */

namespace App\Medinfo\Lexer;
use App\Document;
use App\Row;
use App\Column;
use App\Cell;

//use Carbon\Carbon;

class FoldControlInterpreter extends ControlInterpreter
{
    public $diapason; // первый аргумент диапазон ячеек
    public $divider; // порог срабатывания - число (%)
    public $cellarray = [];
    public $iterationMode = 3; // Режим итерации по ячейкам


    public function setArguments()
    {
        $this->diapason = $this->root->children[0];
        $this->divider = (float)$this->root->children[1]->tokens[0]->text;
        if ($this->divider == 0) {
            throw new InterpreterException("Проверка на кратность нулю невозможна");
        }
        $this->prepareReadable();
        $translater = new ExpressionTranslater($this->form, $this->table);
        $translater->translate($this->diapason);
        $this->fillIncompleteLinks($this->diapason);
        foreach ($this->diapason->children[0]->children as $cellNode) {
            $this->cellarray[] = $cellNode->tokens[0]->text;
        }
        //dd($this->cellarray);
    }

    public function prepareReadable()
    {
        $function_elements = $this->writeReadableCellArray($this->diapason->children[0]);
        $this->readableFormula = 'проверка на кратность ячеек ' . implode(', ', $function_elements) . '. Делитель ' . $this->divider;
        $this->results['formula'] = $this->readableFormula;

    }

    public function exec(Document $document)
    {
        //dd("Функция проверки кратности");
        $this->document = $document;
        $this->results['valid'] = true;
        $this->results['not_in_scope'] = false;
        $this->results['iteration_mode'] = $this->iterationMode;
        foreach($this->cellarray as $key => $cell) {
            try {
                $this->results['iterations'][$key] =  $this->chekFold($cell);
                $this->results['valid'] = $this->results['valid'] && $this->results['iterations'][$key]['valid'];
            }
            catch (InterpreterException $e) {
                $this->errors[] = ['code' => $e->getErrorCode(), 'message' => $e->getMessage() ];
            }

        }
        if (count($this->errors) > 0) {
            $this->results['errors'] = $this->errors;
        }
        return $this->results;
    }

    public function chekFold($cell) {
        $r = [];
        $r['valid'] = true;
        $parsed_adress = ExpressionTranslater::parseCelladress($cell);
        $row = Row::ofTable($this->table->id)->where('row_code', $parsed_adress['r'])->first();
        if (is_null($row)) {
            throw new InterpreterException("Строка с кодом " . $parsed_adress['r'] . " не найдена в таблице (" . $this->table->table_code . ") \""
                . $this->table->table_name . "\" в форме " . $this->form->form_code, 1005);
        }
        $column = Column::ofTable($this->table->id)->where('column_index', $parsed_adress['c'])->first();
        if (is_null($column)) {
            throw new InterpreterException("Графа с индексом " . $parsed_adress['c'] . " не найдена в таблице " . $this->table->table_code
                . ") \"" . $this->table->table_name . "\" в форме " . $this->form->form_code, 1006);
        }
        $current_cell = Cell::ofDTRC($this->document->id, $this->table->id, $row->id, $column->id)->first();

        is_null($current_cell) ? $current_value = 0 : $current_value = $current_cell->value;

        $r['left_part_value'] = $current_value;
        //$r['deviation'] = $current_value % $this->divider;
        //$r['deviation'] = $current_value / $this->divider;
        $r['deviation'] = fmod($current_value, $this->divider);
        if ($r['deviation'] !== (float)0) {
            $r['valid'] = false;
        }
        $r['code'] = 'c.' . $this->pad . $parsed_adress['r'] . ',' . $this->pad . 'г.' . $this->pad . $parsed_adress['c'];
        $r['cells'][] = ['row' => $row->id, 'column' => $column->id ];
        return $r;
    }

}
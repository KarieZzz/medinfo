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
use App\Row;
use App\Column;
use App\Cell;

//use Carbon\Carbon;

class InterannualControlInterpreter extends ControlInterpreter
{
    public $diapason; // первый аргумент диапазон ячеек
    public $threshold; // порог срабатывания - число (%)
    public $cellarray = [];
    public $previous_document;
    public $iterationMode = 3; // Режим итерации по ячейкам


    public function setArguments()
    {
        $this->diapason = $this->root->children[0];
        $this->threshold = $this->root->children[1]->tokens[0]->text;
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
        $this->readableFormula = 'межгодовой контроль ячеек ' . implode(', ', $function_elements) ;
        $this->results['formula'] = $this->readableFormula;

    }

    public function exec(Document $document)
    {

        $this->document = $document;
        $current_period = Period::find($this->document->period_id);
        $previous_period = Period::LastYear($current_period->begin_date->year)->first();
        $this->previous_document = Document::OfTUPF($this->document->dtype, $this->document->ou_id, $previous_period->id, $this->document->form_id)->first();
        //dd($this->previous_document);

        $this->results['valid'] = true;
        //$this->results['not_in_scope'] = false;
        foreach($this->cellarray as $key => $cell) {
            try {
                $this->results['iterations'][$key] =  $this->compare_periods($cell);
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

    public function compare_periods($cell) {
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
        $previous_cell = Cell::ofDTRC($this->previous_document->id, $this->table->id, $row->id, $column->id)->first();
        is_null($current_cell) ? $current_value = 0 : $current_value = $current_cell->value;
        is_null($previous_cell) ? $previous_value = 0 : $previous_value = $previous_cell->value;
        $r['left_part_value'] = $current_value;
        $r['right_part_value'] = $previous_value;
        $r['deviation'] = abs($current_value - $previous_value);
        $r['deviation_relative'] = round($r['deviation'] / $current_value * 100, 2);
        if ($r['deviation_relative'] > $this->threshold) {
            $r['valid'] = false;
        }
        $r['code'] = 'c.' . $this->pad . $parsed_adress['r'] . ',' . $this->pad . 'г.' . $this->pad . $parsed_adress['c'];


        $r['cells'][] = ['row' => $row->id, 'column' => $column->id ];
        return $r;
    }

}
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
    public $this_year_cell;
    public $last_year_cell;
    public $previous_document;
    public $compare_by_diapason;
    public $iterationMode = 3; // Режим итерации по ячейкам


    public function setArguments()
    {
        $translater = new ExpressionTranslater($this->form, $this->table);

        if (count($this->root->children) == 2) {
            $this->compare_by_diapason = true;
            $this->diapason = $this->root->children[0];
            $this->threshold = $this->root->children[1]->tokens[0]->text;
            $this->fillIncompleteLinks($this->diapason);
            $translater->translate($this->diapason);
            foreach ($this->diapason->children[0]->children as $cellNode) {
                $this->cellarray[] = $cellNode->tokens[0]->text;
            }
        } elseif (count($this->root->children) == 3) {
            $this->compare_by_diapason = false;
            $this->fillIncompleteLinks($this->root);
            //dd($this->root);
            $this->this_year_cell = $this->root->children[0]->tokens[0]->text;
            $this->last_year_cell = $this->root->children[1]->tokens[0]->text;
            $this->threshold = $this->root->children[2]->tokens[0]->text;
        } else {

        }
        $this->prepareReadable();

        //dd($this->cellarray);
    }

    public function prepareReadable()
    {
        if ($this->compare_by_diapason) {
            $function_elements = $this->writeReadableCellArray($this->diapason->children[0]);
            $this->readableFormula = 'межгодовой контроль ячеек ' . implode(', ', $function_elements) ;
        } else {
            $function_elements = $this->writeReadableCellArray($this->root);
            $this->readableFormula = 'межгодовой контроль ячеек ' .  $function_elements[0] . ' <=> ' . $function_elements[1];


        }

        $this->results['formula'] = $this->readableFormula;
    }

    public function exec(Document $document)
    {
        $this->results['valid'] = true;
        $this->results['iteration_mode'] = $this->iterationMode;
        $this->results['not_in_scope'] = false;

        $this->document = $document;
        $current_period = Period::find($this->document->period_id);
        $previous_period = Period::LastYear($current_period->begin_date->year)->first();
        $this->previous_document = Document::OfTUPF($this->document->dtype, $this->document->ou_id, $previous_period->id, $this->document->form_id)->first();
        //dd($this->previous_document);
        if (is_null($this->previous_document)) {
            $this->errors[] = ['code' => '1111', 'message' => 'Отчет за прошлый год не найден. Межпериодный контроль невозможен' ];
            $this->results['iterations'][0]['valid'] = true;
            $this->results['errors'] = $this->errors;
            return $this->results;
        }
        if ($this->compare_by_diapason) {
            foreach($this->cellarray as $key => $cell) {
                try {
                    $this->results['iterations'][$key] =  $this->compare_periods_by_diapason($cell);
                    $this->results['valid'] = $this->results['valid'] && $this->results['iterations'][$key]['valid'];
                }
                catch (InterpreterException $e) {
                    $this->errors[] = ['code' => $e->getErrorCode(), 'message' => $e->getMessage() ];
                }

            }
        } else {
            try {
                $this->results['iterations'][0] =  $this->compare_periods_by_cellpair();
                $this->results['valid'] = $this->results['iterations'][0]['valid'];
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

    public function compare_periods_by_diapason($cell) {

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

    public function compare_periods_by_cellpair()
    {
        $r = [];
        $r['valid'] = true;
        $parsed_thisyear_adress = ExpressionTranslater::parseCelladress($this->this_year_cell);

        $row_ty = Row::ofTable($this->table->id)->where('row_code', $parsed_thisyear_adress['r'])->first();
        if (is_null($row_ty)) {
            throw new InterpreterException("Строка с кодом " . $parsed_thisyear_adress['r'] . " не найдена в таблице (" . $this->table->table_code . ") \""
                . $this->table->table_name . "\" в форме " . $this->form->form_code, 1005);
        }
        $column_ty = Column::ofTable($this->table->id)->where('column_index', $parsed_thisyear_adress['c'])->first();
        if (is_null($column_ty)) {
            throw new InterpreterException("Графа с индексом " . $parsed_thisyear_adress['c'] . " не найдена в таблице " . $this->table->table_code
                . ") \"" . $this->table->table_name . "\" в форме " . $this->form->form_code, 1006);
        }
        $current_cell = Cell::ofDTRC($this->document->id, $this->table->id, $row_ty->id, $column_ty->id)->first();

        $parsed_lastyear_adress = ExpressionTranslater::parseCelladress($this->last_year_cell);

        $row_ly = Row::ofTable($this->table->id)->where('row_code', $parsed_lastyear_adress['r'])->first();

        if (is_null($row_ly)) {
            throw new InterpreterException("Строка с кодом " . $parsed_lastyear_adress['r'] . " не найдена в таблице (" . $this->table->table_code . ") \""
                . $this->table->table_name . "\" в форме " . $this->form->form_code, 1005);
        }
        $column_ly = Column::ofTable($this->table->id)->where('column_index', $parsed_lastyear_adress['c'])->first();

        if (is_null($column_ly)) {
            throw new InterpreterException("Графа с индексом " . $parsed_lastyear_adress['c'] . " не найдена в таблице " . $this->table->table_code
                . ") \"" . $this->table->table_name . "\" в форме " . $this->form->form_code, 1006);
        }
        //dd($this->previous_document);
        $previous_cell = Cell::ofDTRC($this->previous_document->id, $this->table->id, $row_ly->id, $column_ly->id)->first();

        is_null($current_cell) ? $current_value = 0 : $current_value = $current_cell->value;
        is_null($previous_cell) ? $previous_value = 0 : $previous_value = $previous_cell->value;
        $r['left_part_value'] = $current_value;
        $r['right_part_value'] = $previous_value;
        $r['deviation'] = abs($current_value - $previous_value);
        $r['deviation_relative'] = round($r['deviation'] / $current_value * 100, 2);
        if ($r['deviation_relative'] > $this->threshold) {
            $r['valid'] = false;
        }
        $r['code'] = 'c.' . $this->pad . $parsed_thisyear_adress['r'] . ',' . $this->pad . 'г.' . $this->pad . $parsed_thisyear_adress['c'] . '<-> '
            . 'c.' . $this->pad . $parsed_lastyear_adress['r'] . ',' . $this->pad . 'г.' . $this->pad . $parsed_lastyear_adress['c'];


        $r['cells'][] = ['row' => $row_ty->id, 'column' => $column_ty->id ];
        return $r;
    }

}
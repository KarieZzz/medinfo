<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2017
 * Time: 16:09
 */

namespace App\Medinfo\DSL;


use App\Album;
use App\AlbumRowSet;
use App\AlbumColumnSet;
use App\Cell;
use App\Column;
use App\Document;
use App\Form;
use App\Table;

class SectionEvaluator extends ControlFunctionEvaluator
{

    public $second_document; // Второй документ, с которым производится сравнение разрезов

    public function setArguments()
    {
        $this->getArgument(1);
        $this->getArgument(2);
        $this->getArgument(3);
    }

    public function evaluate()
    {
        $result['l'] = $this->evaluateSubtree($this->arguments[1]);
        $result['r'] = $this->evaluateSubtree($this->arguments[2]);
        $result['d'] = round(abs($result['l'] - $result['r']),2);
        $result['v'] = $this->compare($result['l'], $result['r'], $this->arguments[3]->content);
        return $result;
    }

    public function makeControl()
    {
        if (!$this->document) {
            throw new \Exception("Документ для проведения контроля не определен");
        }
        $result = [];
/*        $this->not_in_scope = $this->validateDocumentScope();
        dd($this->not_in_scope);
        if ($this->not_in_scope) {
            $result[0]['valid'] = true;
            $this->valid = true;
            return $result;
        }*/
        return $this->compareSection();
    }

    public function compareSection()
    {
        $result = [];
        $errors = [];
        $valid = true;
        $album = Album::find($this->document->album_id);
        $exluded_rows = AlbumRowSet::OfAlbum($album->id)->pluck('id')->toArray();
        $exluded_columns = AlbumColumnSet::OfAlbum($album->id)->pluck('id')->toArray();
        $form_left = Form::OfCode($this->arguments[1]->content)->with(['tables' => function ($query) use ($album) {
            $query->whereDoesntHave('excluded', function ($query) use($album) {
                $query->where('album_id', $album->id);
            })->orderBy('table_index');
        }])->first();
        $form_right = Form::OfCode($this->arguments[2]->content)->with(['tables' => function ($query) use ($album) {
            $query->whereDoesntHave('excluded', function ($query) use($album) {
                $query->where('album_id', $album->id);
            })->orderBy('table_index');
        }])->first();
        //dd($form_right);
        $right_of_left = false;
        $left_of_right = false;
        $related = false;
        if ($form_right->relation === $form_left->id) {
            $right_of_left = true;
            $related = true;
        } elseif ($form_left->relation === $form_right->id) {
            $left_of_right = true;
            $related = true;
        }
        $this->second_document = Document::OfTUPF($this->document->dtype, $this->document->ou_id, $this->document->period_id, $form_right->id)->first();
        if ($related && $right_of_left) {
            foreach($form_left->tables as $table) {
                foreach ($table->rows as $row) {
                    $columns = Column::OfTable($table->id)->OfDataType()->whereDoesntHave('excluded', function ($query) use($album) {
                        $query->where('album_id', $album->id);
                    })->get();
                    //dd($columns);
                    foreach ($columns as $column) {
                        if (!in_array($row->id, $exluded_rows)) {
                            $leftvalue = 0;
                            $rightvalue = 0;
                            $left_cell = Cell::OfDRC($this->document->id, $row->id, $column->id)->first();
                            $right_cell = Cell::OfDRC($this->second_document->id, $row->id, $column->id)->first();
                            if ($left_cell) {
                                $leftvalue = (float)$left_cell->value;
                            }
                            if ($right_cell) {
                                $rightvalue = (float)$right_cell->value;
                            }
                            $v = $this->compare($leftvalue, $rightvalue, $this->arguments[3]->content);
                            $result[] = [
                                'code' => ['table_code' => $table->table_code, 'row_code' => $row->row_code, 'column_code' => $column->column_code],
                                'cells' => ['table' => $table->id, 'row' => $row->id, 'column' => $column->id],
                                'left_part_value' => $leftvalue,
                                'right_part_value' => $rightvalue,
                                'deviation' => round(abs($leftvalue - $rightvalue),2),
                                'valid' => $v
                            ];
                            //$v ?: $errors[] = ['table_code' => $table->table_code, 'row_code' => $row->row_code, 'column_code' => $column->column_code];
                            $valid = $valid && $v;
                        }
                    }

                }
            }
        }
        $this->valid = $valid;
        //dd($errors);
        return $result;
    }

    public function compareRelated()
    {

    }

}
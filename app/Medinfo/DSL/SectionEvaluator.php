<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2017
 * Time: 16:09
 */

namespace App\Medinfo\DSL;


use App\Album;
use App\Document;
use App\Form;

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
        $this->compareSection();
    }

    public function compareSection()
    {
        $album = Album::find($this->document->album_id);
        $form_left = Form::OfCode($this->arguments[1]->content)->first();
        $form_right = Form::OfCode($this->arguments[2]->content)->first();

        $right_of_left = false;
        $left_of_right = false;
        $not_related = true;
        if ($form_right->relation === $form_left->id) {
            $right_of_left = true;
            $not_related = false;
        } elseif ($form_left->relation === $form_right->id) {
            $left_of_right = true;
            $not_related = false;
        }
        $this->second_document = Document::OfTUPF($this->document->dtype, $this->document->ou_id, $this->document->period_id, $form_right->id)->first();
        foreach($form_left->tables as $table) {
            foreach ( $table->rows as $row) {
                dump($row->row_code);
            }
        }

    }

}
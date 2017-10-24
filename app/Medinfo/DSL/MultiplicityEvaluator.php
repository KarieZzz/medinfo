<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2017
 * Time: 16:09
 */

namespace App\Medinfo\DSL;


class MultiplicityEvaluator extends ControlFunctionEvaluator
{

    public function setIterations()
    {
        $this->iterations = $this->properties['iterations'][0];
        //dd($this->iterations);
    }

    public function setArguments()
    {
        $this->getArgument(1);
        $this->getArgument(2);
    }

    public function prepareCellValues()
    {
        foreach ($this->iterations as &$cell_adress) {
            $cell = \App\Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
            !$cell ? $value = 0 : $value = (float)$cell->value;
            $cell_adress['value'] = $value;
        }
    }

    public function makeControl()
    {
        if (!$this->validateDocumentScope()) {
            $this->not_in_scope = true;
        }
        $this->prepareCellValues();
        $this->prepareCAstack();
        $result = [];
        $valid = true;
        $i = 0;
        //dd($this->iterations);
        foreach ($this->iterations as $cell_label => $props) {
            //$node = $this->caStack[$cell_label];
            //$node->type = ControlFunctionLexer::NUMBER;
            //$node->content = $props['value'];
            $result[$i]['cells'][] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            $result[$i]['code'] = 'с.' . $props['codes']['r'] . ' г.' . $props['codes']['c'];
            $result[$i]['left_part_value'] = $props['value'];
            $result[$i]['right_part_value'] = null;
            $result[$i]['deviation'] = null;
            $result[$i]['boolean_op'] = null;
            //$result[$i]['divider'] = $this->arguments[2]->content;
            $result[$i]['valid'] = $this->multiplicity($props['value'], $this->arguments[2]->content);
            $valid = $valid &&  $result[$i]['valid'];
            $i++;
        }
        $this->valid = $valid;
        return $result;
    }



}
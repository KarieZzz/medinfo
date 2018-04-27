<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2017
 * Time: 16:09
 */

namespace App\Medinfo\DSL;


class IPdiapazonEvaluator extends ControlFunctionEvaluator
{

    public $previous_document;
    public $boolean_op;
    public $threshold = 0;

    public function setIterations()
    {
        $this->iterations = $this->properties['iterations'][0];
        //dd($this->iterations);
    }

    public function setArguments()
    {
        $this->getArgument(1);
        $this->getArgument(2);
        $this->boolean_op = $this->arguments[2]->content;
        if (isset($this->pTree->children[3]->children[0])) {
            $this->threshold = $this->pTree->children[3]->children[0]->content;
        }

    }

    public function prepareCellValues()
    {
        $previous_period = $this->getPreviousRelativePeriod();
        if (!$previous_period) {
            throw new \Exception('Прошлогодний отчетный период не найден');
        }
        $this->previous_document = \App\Document::OfTUPF($this->document->dtype, $this->document->ou_id, $previous_period->id, $this->document->form_id)->first();
        if (!$this->previous_document) {
            throw new \Exception('Документ за прошлогодний отчетный период по данной организиционной единице не найден');
        }
        //dd($this->previous_document);
        foreach ($this->iterations as &$cell_adress) {
            $ccell = \App\Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
            !$ccell ? $cvalue = 0 : $cvalue = (float)$ccell->value;
            $cell_adress['value'] = $cvalue;

            $pcell = \App\Cell::OfDRC($this->previous_document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
            !$pcell ? $pvalue = 0 : $pvalue = (float)$pcell->value;
            $cell_adress['prev_value'] = $pvalue;
        }
    }

    public function makeControl()
    {
        $this->not_in_scope = $this->validateDocumentScope();
        $result = [];
        if ($this->not_in_scope) {
            $result[0]['valid'] = true;
            $this->valid = true;
            return $result;
        }
        $this->prepareCellValues();
        $this->prepareCAstack();
        $result = [];
        $valid = true;
        $i = 0;
        foreach ($this->iterations as $cell_label => $props) {
            $result[$i]['cells'][] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            $result[$i]['code'] = 'с.' . $props['codes']['r'] . ' г.' . $props['codes']['c'];
            $result[$i]['left_part_value'] = $props['value'];
            $result[$i]['right_part_value'] = $props['prev_value'];
            $diff = abs($props['prev_value'] - $props['value']);
            if ($diff > 0 && $props['prev_value'] !== 0) {
                $increment = round($diff/$props['prev_value']*100, 2) ;
            } elseif($diff > 0 && $props['prev_value'] === 0) {
                $increment = 100;
            } else {
                $increment = 0;
            }
            $result[$i]['deviation'] = $increment;
            $result[$i]['boolean_op'] = $this->boolean_op;
            $threshold_overlap = $increment > $this->threshold ? false : true;
            $compare_result = $this->compare($result[$i]['left_part_value'], $result[$i]['right_part_value'], $this->boolean_op);
            // TODO: Реализовано на данный момент без учета перекрытия порога. Нужно подумать нужен ли этот функционал?
            $result[$i]['valid'] = $compare_result;
            $valid = $valid &&  $result[$i]['valid'];
            $i++;
        }
        $this->valid = $valid;
        return $result;
    }



}
<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2017
 * Time: 16:09
 */

namespace App\Medinfo\DSL;


class InterannualEvaluator extends ControlFunctionEvaluator
{

    public $previous_document;
    public $threshold = 0;
    public $markOnlyFirstArg = true;

    public function setArguments()
    {
        $this->getArgument(1);
        $this->getArgument(2);
        $this->getArgument(3);
        $this->threshold = $this->arguments[3]->content;
    }

    public function evaluate()
    {
        $result['l'] = $this->evaluateSubtree($this->arguments[1]);
        $result['r'] = $this->evaluateSubtree($this->arguments[2]);
        $result['d'] = round(abs($result['l'] - $result['r']),2);
        $result['v'] = $this->compare($result['l'], $result['r'], $this->arguments[3]->content);
        if ($result['d'] > 0 && $result['r'] !== 0) {
            $increment = round($result['d']/$result['r']*100, 1) ;
        } elseif($result['d'] > 0 && $result['r'] === 0) {
            $increment = 100;
        } else {
            $increment = 0;
        }
        $result['d'] = $increment;
        $result['v'] = $increment > $this->threshold ? false : true;
        return $result;
    }

    public function prepareCellValues()
    {
        $current_period = \App\Period::find($this->document->period_id);
        $previous_period = \App\Period::PreviousYear($current_period->begin_date->year)->first();
        if (!$previous_period) {
            throw new \Exception('Прошлогодний отчетный период не найден');
        }
        $this->previous_document = \App\Document::OfTUPF($this->document->dtype, $this->document->ou_id, $previous_period->id, $this->document->form_id)->first();
        if (!$this->previous_document) {
            throw new \Exception('Документ за прошлогодний отчетный период по данной организиционной единице не найден');
        }
        foreach ($this->iterations as &$cell_adresses) {
            foreach ($cell_adresses as $key => &$cell_adress) {
                $arg = $cell_adress['arg'];
                $cell = null;
                if ($arg === 0) {
                    $cell = \App\Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                } elseif ($arg === 1) {
                    $cell = \App\Cell::OfDRC($this->previous_document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                }
                !$cell ? $value = 0 : $value = (float)$cell->value;
                $cell_adress['value'] = $value;
            }
        }
    }

}
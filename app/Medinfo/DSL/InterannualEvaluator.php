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

    public function setArguments()
    {
        $this->getArgument(1);
        $this->getArgument(2);
        $this->getArgument(3);
        $this->threshold = $this->arguments[3]->content;
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
        $dtype = $this->document->dtype;
        $ou_id = $this->document->ou_id;
        $period_id = $this->document->period_id;
        dd($this->iterations);
        foreach ($this->iterations as &$cell_adresses) {
            foreach ($cell_adresses as &$cell_adress) {
                if ($cell_adress['ids']['p'] === $period_id) {
                    $cell = Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                } else {
                    $document = Document::OfTUPF($dtype, $ou_id, $period_id, $cell_adress['ids']['f'])->first();
                    $cell = $document ? Cell::OfDRC($document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']) : null;
                }
                !$cell ? $value = 0 : $value = (float)$cell->value;
                $cell_adress['value'] = $value;
            }
        }
    }

    public function makeControl()
    {
        $this->prepareCellValues();
        $this->prepareCAstack();
        $result = [];
        $valid = true;
        $i = 0;
        //dd($this->iterations);
        foreach ($this->iterations as $cell_label => $props) {
            $result[$i]['cells'][] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            $result[$i]['code'] = 'с.' . $props['codes']['r'] . ' г.' . $props['codes']['c'];
            $result[$i]['left_part_value'] = $props['value'];
            $result[$i]['right_part_value'] = $props['prev_value'];
            $diff = abs($props['prev_value'] - $props['value']);
            if ($diff > 0 && $props['prev_value'] !== 0) {
                $increment = round($diff/$props['prev_value']*100, 1) ;
            } elseif($diff > 0 && $props['prev_value'] === 0) {
                $increment = 100;
            } else {
                $increment = 0;
            }
            $result[$i]['deviation'] = $increment;
            $result[$i]['boolean_op'] = null;
            $result[$i]['valid'] = $increment > $this->threshold ? false : true;
            $valid = $valid &&  $result[$i]['valid'];
            $i++;
        }
        $this->valid = $valid;
        return $result;
    }



}
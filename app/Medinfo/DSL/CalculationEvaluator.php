<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 13.12.2017
 * Time: 17:59
 */

namespace App\Medinfo\DSL;


class CalculationEvaluator extends CalculationFunctionEvaluator
{
    public function setArguments()
    {
        $this->getArgument(1);
    }

    public function makeConsolidation()
    {
        $period_id = $this->document->period_id;
        $this->prepareCAstack();
        foreach ($this->properties['units'] as $ou_id) {
            foreach ($this->iterations[0] as &$cell_adress) {
                $document = \App\Document::Primary()->OfUPF($ou_id, $period_id, $cell_adress['ids']['f'])->first();
                $cell = $document ? \App\Cell::OfDRC($document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']) : null;
                !$cell ? $value = 0 : $value = (float)$cell->value;
                $cell_adress['value'] = $value;
            }
            $cells = $this->convertCANodes($this->iterations[0]);
            $this->calculatedValue += $this->evaluateSubtree($this->arguments[1]);
        }

        //dd($this);

    }

    public function evaluate()
    {
        //dd($this->arguments[1]);
        //$result = $this->evaluateSubtree($this->arguments[1]);
        //dd($result);
        //return count($this->properties['units']);
        return $this->calculatedValue;
    }


}
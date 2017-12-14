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

    public function evaluate()
    {
        dd($this->document->period);
        return count($this->properties['units']);
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 12.12.2017
 * Time: 10:38
 */

namespace App\Medinfo\DSL;


class UnitCountEvaluator extends CalculationFunctionEvaluator
{
    public function makeConsolidation()
    {
        foreach ($this->properties['units'] as $ou_id) {
            $this->logIteration($ou_id, 1);
        }
    }

    public function evaluate()
    {
        return count($this->properties['units']);
    }
}
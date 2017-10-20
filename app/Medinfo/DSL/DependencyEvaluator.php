<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2017
 * Time: 16:09
 */

namespace App\Medinfo\DSL;


class DependencyEvaluator extends ControlFunctionEvaluator
{

    public function setArguments()
    {
        $this->getArgument(1);
        $this->getArgument(2);
    }

    public function evaluate()
    {
        $result['l'] = $this->evaluateSubtree($this->arguments[1]);
        $result['r'] = $this->evaluateSubtree($this->arguments[2]);
        $result['d'] = abs($result['l'] - $result['r']);
        $result['v'] = $this->compare($result['l'], $result['r'], '^');
        return $result;
    }

}
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
    }

    public function setArguments()
    {
        $this->getArgument(1);
        $this->getArgument(2);
    }

    public function evaluate()
    {
        $result['l'] = $this->arguments[1]->content;
        $result['r'] = null;
        $result['d'] = null;
        $result['v'] = $this->multiplicity($this->arguments[1]->content, $this->arguments[2]->content);
        return $result;
    }

}
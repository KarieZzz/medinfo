<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 15.08.2017
 * Time: 17:14
 */

namespace App\Medinfo\DSL;


class EvaluatorExample
{
    //
    public $parceTree;

    public function __construct(ParseTree $parseTree)
    {
        $this->parceTree = $parseTree;
    }

    public function evaluate()
    {
        return $this->evaluateSubtree($this->parceTree);
    }

    public function evaluateSubtree(ParseTree $node)
    {
        if (CalculationFunctionLexer::$tokenNames[$node->type] === 'NUMBER') {
            return $node->content;
        } else {
            $left = $this->evaluateSubtree($node->left());
            $right = $this->evaluateSubtree($node->right());
            switch (CalculationFunctionLexer::$tokenNames[$node->type]) {
                case 'PLUS' :
                    return $left + $right;
                    break;
                case 'MINUS' :
                    return $left - $right;
                    break;
                case 'MULTIPLY' :
                    return $left * $right;
                    break;
                case 'DIVIDE' :
                    //dump($right);
                    if ($right === 0) {
                        return 0;
                        break;
                    }
                    return $left / $right;
                    break;
            }
        }
        return null;
    }

}
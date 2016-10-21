<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2016
 * Time: 9:38
 */

namespace App\Medinfo\Lexer;


class CompareControlInterpreter
{
    public $root;
    public $lp_expression; // node левая часть сравнения
    public $rp_expression; // node правая часть сравнения
    public $boolean; // знак сравнения в контроле
    public $unit_scope; // область приложения функции (группы учреждений)
    public $iteration_mode; // режим перебора - без перебора(null) по строкам(1) и графам(2) при внутритабличном контроле
    public $iteration_range; // собственно диапазон строк или граф для подстановки значений

    public function __construct(ParseTree $root)
    {
        $this->root = $root;
        $this->setArguments();
    }

    public function setArguments()
    {
        $this->lp_expression = $this->root->children[0];
        $this->rp_expression = $this->root->children[1];
        $this->boolean = $this->root->children[2]->tokens[0]->text;
        if (isset($this->root->children[3]->children[0]->tokens[0])) {
            $this->unit_scope = $this->root->children[3]->children[0]->tokens[0]->text;
        }

        if (count($this->root->children[4]->children[0]->tokens)) {
            $this->iteration_range = $this->root->children[4]->children[0]->tokens[0]->text;
        }
        $this->iteration_mode = $this->root->children[4]->tokens[0]->text == 'строки' ? 1 : 2;
    }

    public function exec() {
        $this->rewrite_summfunction($this->rp_expression);
    }

    public function rewrite_summfunction($expression)
    {
        $elementcount = count($expression->children);
        for ($i = 0; $i < $elementcount; $i++) {
            $element = $expression->children[$i];
            if ($element->rule == 'element') {
                echo $element->children[0]->rule;
            }
        }
    }

}
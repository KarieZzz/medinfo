<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2016
 * Time: 7:35
 */

namespace App\Medinfo\Lexer;


abstract class ParseTree
{
    public $rule;
    public $tokens = [];
    public $children; // normalized child list

    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    public function __toString() {
        return "<" . $this->rule . ">";
    }

    public function addToken(Token $t)
    {
        $this->tokens[] = $t;
    }

    public function addChild(ParseTree $pt)
    {
        if ($this->children == null) {
            $this->children = [];
        }
        $this->children[] = $pt;
    }


}
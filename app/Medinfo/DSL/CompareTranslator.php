<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 10:29
 */

namespace App\Medinfo\DSL;


class CompareTranslator extends ControlPtreeTranslator
{

    public $boolean_sign;

    public function makeReadable() {
        foreach ($this->parser->argStack[0] as $node) {
            $this->scriptReadable .= $node;
        }
        $this->boolean_sign = ' ' . $this->parser->root->children[2]->children[0]->content;
        $this->scriptReadable .= $this->boolean_sign;
        foreach ($this->parser->argStack[1] as $node) {
            $this->scriptReadable .= $node;
        }
        $this->scriptReadable = str_replace(['  ', '( '], [' ', '('], $this->scriptReadable);
    }
}
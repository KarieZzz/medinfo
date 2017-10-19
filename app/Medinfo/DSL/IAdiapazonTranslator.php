<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 10:29
 */

namespace App\Medinfo\DSL;


class IAdiapazonTranslator extends ControlPtreeTranslator
{

    public function makeReadable() {
        $this->scriptReadable = "данные ячейки входящей в ";
        foreach ($this->parser->argStack[0] as $node) {
            $this->scriptReadable .= $node;
        }
        $threshold = $this->parser->root->children[1]->children[0];
        if ($threshold->content !== '0') {
            $this->scriptReadable .= " отличаются от прошлого года более чем на ";
            $this->scriptReadable .= $this->parser->root->children[1]->children[0];
            $this->scriptReadable .= "%";
        } else {
            $this->scriptReadable .= " отличаются от прошлого года";
        }
        $this->scriptReadable = str_replace(['  ', '( '], [' ', '('], $this->scriptReadable);
    }
}
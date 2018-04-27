<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 10:29
 */

namespace App\Medinfo\DSL;


class IPdiapazonTranslator extends ControlPtreeTranslator
{

    public function makeReadable() {
        //dd($this->parser->argStack);
        $this->scriptReadable = "данные ячейки входящей в ";
        foreach ($this->parser->argStack[0] as $node) {
            $this->scriptReadable .= $node;
        }
        $bool = $this->parser->root->children[1]->children[0];
        if (isset($this->parser->root->children[3]->children[0])) {
            $threshold = $this->parser->root->children[3]->children[0]->content;
        } else {
            $threshold = '0';
        }
        if ($threshold !== '0') {
            $this->scriptReadable .=  " должны быть " . $bool . " значений прошлого периода (при допустимой разнице не более " . $threshold . "%)";
        } else {
            $this->scriptReadable .=  " должны быть " . $bool . " значений прошлого периода";
        }
        $this->scriptReadable = str_replace(['  ', '( '], [' ', '('], $this->scriptReadable);
    }
}
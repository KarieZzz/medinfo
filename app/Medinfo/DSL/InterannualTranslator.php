<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 10:29
 */

namespace App\Medinfo\DSL;


class InterannualTranslator extends ControlPtreeTranslator
{

    public function makeReadable() {
        foreach ($this->parser->argStack[0] as $node) {
            $this->scriptReadable .= $node;
        }
        $this->scriptReadable .= " (текущий год) отличается от ";
        foreach ($this->parser->argStack[1] as $node) {
            $this->scriptReadable .= $node;
        }
        $threshold = $this->parser->root->children[2]->children[0];
        if ($threshold->content !== '0') {
            $this->scriptReadable .= " (прошлый год) более чем на ";
            $this->scriptReadable .= $this->parser->root->children[2]->children[0] . "%";
        } else {
            $this->scriptReadable .= " (прошлый год)";
        }
        $this->scriptReadable = str_replace(['  ', '( '], [' ', '('], $this->scriptReadable);
    }
}
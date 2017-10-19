<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 10:29
 */

namespace App\Medinfo\DSL;


class DependencyTranslator extends ControlPtreeTranslator
{

    public function makeReadable() {
        $this->scriptReadable = "если ";
        foreach ($this->parser->argStack[0] as $node) {
            $this->scriptReadable .= $node;
        }
        $this->scriptReadable .= "  &ne; 0, то ";
        foreach ($this->parser->argStack[1] as $node) {
            $this->scriptReadable .= $node;
        }
        $this->scriptReadable .= " &ne; 0 и наоборот";
        $this->scriptReadable = str_replace(['  ', '( '], [' ', '('], $this->scriptReadable);
    }
}
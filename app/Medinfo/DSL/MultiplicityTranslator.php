<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 10:29
 */

namespace App\Medinfo\DSL;


class MultiplicityTranslator extends ControlPtreeTranslator
{

    public function makeReadable() {
        $this->scriptReadable = "проверка на кратность ячеек: ";
        foreach ($this->parser->argStack[0] as $node) {
            $this->scriptReadable .= $node;
        }
        $this->scriptReadable .= ". Делитель: ";
        $this->scriptReadable .= $this->parser->root->children[1]->children[0];
        $this->scriptReadable = str_replace(['  ', '( '], [' ', '('], $this->scriptReadable);
    }
}
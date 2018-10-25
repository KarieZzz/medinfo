<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 10:29
 */

namespace App\Medinfo\DSL;


class SectionTranslator extends ControlPtreeTranslator
{

    public $boolean_sign;

    public function makeReadable() {
        $this->scriptReadable = "данные ячейки входящей в форму ";
        foreach ($this->parser->argStack[0] as $node) {
            $this->scriptReadable .= $node;
        }
        $this->boolean_sign = ' ' . $this->parser->root->children[2]->children[0]->content;
        $this->scriptReadable .= $this->boolean_sign;
        $this->scriptReadable .= " данных ячейки входящей в форму ";
        foreach ($this->parser->argStack[1] as $node) {
            $this->scriptReadable .= $node;
        }
        $this->scriptReadable = str_replace(['  ', '( '], [' ', '('], $this->scriptReadable);
    }

    public function getProperties() {
        $properties = parent::getProperties();
        $properties['iteration_mode'] = self::MIXED; // Итерация и по строкам и по графам
        $properties['type'] = 2; // Тип контроля межформенный
        return $properties;
    }

}
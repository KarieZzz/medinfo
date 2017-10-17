<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2016
 * Time: 8:27
 */

namespace App\Medinfo\DSL;


class ControlFunctionParseTree extends ParseTree
{
    public function __toString()
    {
        switch ($this->type) {
            case ControlFunctionLexer::CELLADRESS:
                $s = $this->humanizeCA($this->content);
                return $s;
            case ControlFunctionLexer::NAME:
                $s = $this->content . '( ';
                $a = [];
                foreach ($this->children as $child) {
                    if ($child->type === ControlFunctionLexer::CELLRANGE) {
                        $range = explode(' ', $child->content);
                        $a[] = $this->humanizeCA($range[0]) . ' по ' . $this->humanizeCA($range[1]);
                    } elseif ($child->type === ControlFunctionLexer::CELLADRESS) {
                        $a[] = $this->humanizeCA($child->content);
                    }
                    //$s .= 't: ' . $child->type . $child->content;
                }
                return $s . implode(', ', $a) . ')';
            case ControlFunctionLexer::PLUS:
            case ControlFunctionLexer::MINUS:
            case ControlFunctionLexer::MULTIPLY:
            case ControlFunctionLexer::DIVIDE:
                return ' ' . $this->content . ' ';
            case ControlFunctionLexer::BOOLEAN:
                return ' ' . $this->content;
            default:
                return ' ' . $this->content;
        }
        //return "||" . $this->type . ', '. $this->content . "||";
    }

    public function humanizeCA($cadress)
    {
        return str_replace(['Ф', 'Т', 'С', 'Г', 'П0'] , [' ф.', ' т.', ' с.', ' г.', ' прошл.'], $cadress);
    }
}
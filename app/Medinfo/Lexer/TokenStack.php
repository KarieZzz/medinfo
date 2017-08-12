<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 17.10.2016
 * Time: 14:42
 */

namespace App\Medinfo\Lexer;


class TokenStack
{
    public $stack = [];
    public $count = 0;

    public function push($type, $value) {
        //$this->stack[$this->count++] = array('type' => $type, 'text' => $value );
        $token = new Token($type, $value);
        $this->stack[$this->count++] = $token;
        return $token;
    }

    public function pop() {
        if ($this->count > 0) {
            return $this->stack[--$this->count];
        }
        return null;
    }

    public function last($n = 1) {
        if ($this->count - $n < 0) {
            return null;
        }
        return $this->stack[$this->count - $n];
    }
}
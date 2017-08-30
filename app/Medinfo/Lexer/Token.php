<?php

namespace App\Medinfo\Lexer;

class Token {
    public $type;
    public $text;
    
    public function __construct($type, $text) {
        $this->type = $type;
        $this->text = $text;
    }
    
    public function __toString() {
        $tname = \App\Medinfo\DSL\CalculationFunctionLexer::$tokenNames[$this->type];
        return "<'" . $this->text . "', " . $tname . ">";
    }
}

?>
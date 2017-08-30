<?php

namespace App\Medinfo\DSL;

class CalculationToken extends Token {
    public $type;
    public $text;
    
    public function __construct($type, $text) {
        parent::__construct($type, $text);
    }
    
    public function __toString() {
        $tname = CalculationFunctionLexer::$tokenNames[$this->type];
        return "<'" . $this->text . "', " . $tname . ">";
    }
}

?>
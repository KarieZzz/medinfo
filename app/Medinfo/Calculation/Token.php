<?php

namespace App\Medinfo\Calculation;

class Token {
    public $type;
    public $text;
    
    public function __construct($type, $text) {
        $this->type = $type;
        $this->text = $text;
    }

    public function __toString() {
        $tname = CalculationFunctionLexer::$tokenNames[$this->type];
        return "<'" . $this->text . "', " . $tname . ">";
    }
}

?>
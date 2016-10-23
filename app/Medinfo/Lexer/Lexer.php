<?php

namespace App\Medinfo\Lexer;

abstract class Lexer {

    const EOF       = -1; // represent end of file char
    const EOF_TYPE  = 1;  // represent EOF token type
    const INVALID_TOKEN_TYPE = 0; // to be explicit
    protected $input;     // input string
    protected $p = 0;     // index into input of current character
    protected $c;         // current character
    public $tokenstack; // token buffer
    public static $tokenNames = [ ];

    public function __construct($input) {
        $this->input = $input;
        // prime lookahead
        $this->c = mb_substr($input, $this->p, 1);
        $this->tokenstack = new TokenStack();
    }

    /** Move one character; detect "end of file" */
    public function consume() {
        $this->p++;
        //if ($this->p >= strlen($this->input)) {
        if ($this->p >= mb_strlen($this->input)) {
            $this->c = Lexer::EOF;
        }
        else {
            $this->c = mb_substr($this->input, $this->p, 1);
            //$this->c = substr($this->input, $this->p, 1);
        }
    }

    public function match($x) {
        if ( $this->c == $x) {
            $this->consume();
        } else {
            throw new \Exception("Ожидался символ " . $x . "; найден " . $this->c );
        }
    }

    public abstract function nextToken();
    public abstract function getTokenName($tokenType);
}

?>
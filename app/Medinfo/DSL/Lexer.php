<?php

namespace App\Medinfo\DSL;

abstract class Lexer {

    const EOF                   = -1;
    const EOF_TYPE              = 1;
    const INVALID_TOKEN_TYPE    = 0;
    protected $input;                           // обрабатываемая строка
    protected $p                = 0;            // позиция текущего символа
    protected $c;                               // текущий символ
    protected $tokenstack;
    protected $celladressStack;
    public static $tokenNames = [ ];

    public function __construct($input) {
        $this->input = $input;
        // prime lookahead
        $this->c = mb_substr($input, $this->p, 1);
        $this->tokenstack = new \SplDoublyLinkedList;
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
    public abstract function getTokenType($tokenType);


}

?>
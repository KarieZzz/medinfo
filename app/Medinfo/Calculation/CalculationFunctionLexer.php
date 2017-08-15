<?php

namespace App\Medinfo\Calculation;

class CalculationFunctionLexer extends Lexer {

    const LPARENTH      = 2;
    const RPARENTH      = 3;
    const PLUS          = 4;
    const MINUS         = 5;
    const MULTIPLY      = 6;
    const DIVIDE        = 7;
    const NUMBER        = 8;
    const COLUMNADRESS  = 9;
    //const EXPRESSION    = 10;

    public static $tokenNames = [
        "n/a",
        "EOF",
        "LPARENTH",
        "RPARENTH",
        "PLUS",
        "MINUS",
        "MULTIPLY",
        "DIVIDE",
        "NUMBER",
        "COLUMNADRESS",
        //"EXPRESSION",
    ];
    
    public function __construct($input)
    {
        parent::__construct($input);
    }

    public function getTokenName($x)
    {
        return self::$tokenNames[$x];
    }

    public function getTokenType($x)
    {
        return self::$tokenNames[$x];
    }

    public function nextToken()
    {
        while ( $this->c != self::EOF ) {
            switch ( $this->c ) {
                case " " :
                case "\t":
                case "\n":
                case "\r":
                    $this->ws();
                    continue;
                case '(' :
                    $this->consume();
                    $token = new Token(self::LPARENTH, '(');
                    $this->tokenstack->push($token);
                    return $token;
                case ')' :
                    $this->consume();
                    $token = new Token(self::RPARENTH, ')');
                    $this->tokenstack->push($token);
                    return $token;
                case '*' :
                    $this->consume();
                    $token = new Token(self::MULTIPLY, '*');
                    $this->tokenstack->push($token);
                    return $token;
                case '/' :
                    $this->consume();
                    $token = new Token(self::DIVIDE, '/');
                    $this->tokenstack->push($token);
                    return $token;
                case '+' :
                    $this->consume();
                    $token = new Token(self::PLUS, '+');
                    $this->tokenstack->push($token);
                    return $token;
                case '-' :
                    $this->consume();
                    $token = new Token(self::MINUS, '-');
                    $this->tokenstack->push($token);
                    return $token;
                case '.' :
                case $this->c >= '1' && $this->c <= '9':
                return $this->number();
                case 'Г':
                    return $this->columnAdress();
                default :
                    throw new \Exception("Неверный символ: " . $this->c);
            }
        }
        $token = new Token(self::EOF_TYPE,"EOF");
        $this->tokenstack->push($token);
        return $token;
    }

    public function isNUMBER()
    {
        return $this->c == '.' || ($this->c >= '0' && $this->c <= '9');
    }

    public function number()
    {
        $buf = '';
        $decimal_separator = false;
        $token_type = self::NUMBER;
        do {
            if ($this->c === '.') {
                if ($decimal_separator) {
                    //$token_type = self::CODE;
                    //throw new \Exception("Лишний десятичный разделитель в числе " . $buf);
                } else {
                    $decimal_separator = true;
                }
            }
            $buf .= $this->c;
            $this->consume();
        } while ($this->isNUMBER());
        $token = new Token($token_type, $buf);
        $this->tokenstack->push($token);
        return $token;
    }

    public function columnAdress()
    {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isCODE());
        $token = new Token(self::COLUMNADRESS, $buf);
        $this->tokenstack->push($token);
        return $token;
    }

    public function isCODE()
    {
        return $this->c >= '0' && $this->c <= '9';
    }

    /** WS : (' '|'\t'|'\n'|'\r')* ; // игнорируем все пробелы, табуляции, переносы строк ... */
    public function ws()
    {
        while(ctype_space($this->c)) {
            $this->consume();
        }
    }

    public function getTokenStack()
    {
        //$this->tokenstack->push(new Token(self::EXPRESSION, 'Calculation'));
        $token = $this->nextToken();
        while($token->type !== CalculationFunctionLexer::EOF_TYPE) {
            $token = $this->nextToken();
        }
        return $this->tokenstack;
    }
}

?>
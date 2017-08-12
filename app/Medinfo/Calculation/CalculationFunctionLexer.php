<?php

namespace App\Medinfo\Calculation;

class CalculationFunctionLexer extends Lexer {

    const LPARENTH      = 2;
    const RPARENTH      = 3;
    const OPERATOR      = 4;
    const OPERAND       = 5;
    const COLUMNADRESS  = 6;
    //const EXPRESSION    = 7;

    public static $tokenNames = [
        "n/a",
        "EOF",
        "LPARENTH",
        "RPARENTH",
        "OPERATOR",
        "OPERAND",
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
                case '/' :
                case '+' :
                case '-' :
                    return $this->operator();
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

    public function operator()
    {
        switch ($this->c) {
            case '*' :
                $this->consume();
                $token = new Token(self::OPERATOR, '*');
                $this->tokenstack->push($token);
                return $token;
            case '/' :
                $this->consume();
                $token = new Token(self::OPERATOR, '/');
                $this->tokenstack->push($token);
                return $token;
            case '+' :
                $this->consume();
                $token = new Token(self::OPERATOR, '+');
                $this->tokenstack->push($token);
                return $token;
            case '-' :
                $this->consume();
                $token = new Token(self::OPERATOR, '-');
                $this->tokenstack->push($token);
                return $token;
        }
        return true;
    }

    public function number()
    {
        $buf = '';
        $decimal_separator = false;
        $token_type = self::OPERAND;
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

    /** WS : (' '|'\t'|'\n'|'\r')* ; // ignore any whitespace */
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
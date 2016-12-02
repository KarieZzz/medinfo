<?php

namespace App\Medinfo\Lexer;

class ControlFunctionLexer extends Lexer {

    const COMMA         = 2;
    const LBRACK        = 3;
    const RBRACK        = 4;
    const LPARENTH      = 5;
    const RPARENTH      = 6;
    const NAME          = 7;
    const OPERATOR      = 8;
    const DIVIDE        = 9;
    const MULTIPLY      = 10;
    const BOOLEAN       = 11;
    const COLON         = 12;
    const FORMADRESS    = 13;
    const TABLEADRESS   = 14;
    const ROWADRESS     = 15;
    const COLUMNADRESS  = 16;
    const NUMBER        = 17;
    const CELLADRESS    = 18;
    const EXCLAMATION   = 19;
    const CODE          = 20;

    public static $tokenNames = [
        "n/a",
        "EOF",
        "COMMA",
        "LBRACK",
        "RBRACK",
        "LPARENTH",
        "RPARENTH",
        "NAME",
        "OPERATOR",
        "DIVIDE",
        "MULTIP",
        "BOOLEAN",
        "COLON",
        "FORMADRESS",
        "TABLEADRESS",
        "ROWADRESS",
        "COLUMNADRESS",
        "NUMBER",
        "CELLADRESS",
        "EXCLAMATION",
        "CODE",
    ];
    
    public function getTokenName($x)
    {
        return self::$tokenNames[$x];
    }

    public function __construct($input)
    {
        parent::__construct($input);
    }

    public function isCODE()
    {
        return $this->c >= '0' && $this->c <= '9';
    }
    // имя функции строчными буквами на кириллице
    public function isFUNCNAME()
    {
        return $this->c >= 'а' && $this->c <= 'я';
    }

    public function isNUMBER()
    {
        return $this->c == '.' || ($this->c >= '0' && $this->c <= '9');
    }
    // Для кода формы допустимые символя - цифры, строчные кириллические буквы, точка, дефис
    public function isFORMCODE()
    {
        return
        $this->c != 'Т' && (
            ($this->c >= '0' && $this->c <= '9') ||
            //($this->c >= 'А' && $this->c <= 'Я') ||
            ($this->c >= 'а' && $this->c <= 'я') ||
            $this->c == '.' ||
            //$this->c == '_' ||
            $this->c == '-'
        );
    }

    public function isTABLECODE()
    {
        return
            $this->c != 'С' &&
            $this->c >= '0' &&
            $this->c <= '9';
    }

    public function isROWCODE()
    {
        return
            $this->c != 'Г' && (
                ($this->c >= '0' && $this->c <= '9') || $this->c == '.'
            );
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
                case ',' :
                    $this->consume();
                    return $this->tokenstack->push(self::COMMA, ",");
                case '[' :
                    $this->consume();
                    return $this->tokenstack->push(self::LBRACK, '[');
                case ']' :
                    $this->consume();
                    return $this->tokenstack->push(self::RBRACK, ']');
                case '(' :
                    $this->consume();
                    return $this->tokenstack->push(self::LPARENTH, '(');
                case ')' :
                    $this->consume();
                    return $this->tokenstack->push(self::RPARENTH, ')');
                case ':' :
                    $this->consume();
                    return $this->tokenstack->push(self::COLON, ':');
                case '+' :
                case '-' :
                    return $this->operator();
                case '*' :
                    $this->consume();
                    return $this->tokenstack->push(self::MULTIPLY, '*');
                case '!' :
                    $this->consume();
                    return $this->tokenstack->push(self::EXCLAMATION, '!');
                case '/' :
                    $this->consume();
                    return $this->tokenstack->push(self::DIVIDE, '/');
                case '=' :
                case '>' :
                case '<' :
                    return $this->boolean_sign();
                case '.' :
                case $this->c >= '1' && $this->c <= '9':
                    return $this->number();
                case $this->c >= 'а' && $this->c <= 'я':
                    return $this->function_name();
                //case $this->c === '0':
                case 'Ф':
                case 'Т':
                case 'С':
                case 'Г':
                    return $this->cellAdress();

/*                case 'Ф':
                    return $this->formAdress();
                case 'Т':
                    return $this->tableAdress();
                case 'С':
                    return $this->rowAdress();
                case 'Г':
                    return $this->columnAdress();*/
                default :
                    throw new \Exception("Неверный символ: " . $this->c);
            }
        }
        return $this->tokenstack->push(self::EOF_TYPE,"EOF");
    }

    public function boolean_sign()
    {
        $buf = $this->c;
        $this->consume();
        if ($this->c == '=') {
            $buf .= $this->c;
            $this->consume();
        }
        return $this->tokenstack->push(self::BOOLEAN, $buf);
    }

    public function operator()
    {
        $operator = $this->c == '+' ? '+' : '-';
        $this->consume();
        return $this->tokenstack->push(self::OPERATOR, $operator);
    }

    public function function_name()
    {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isFUNCNAME());
        return $this->tokenstack->push(self::NAME, $buf);
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
        return $this->tokenstack->push($token_type, $buf);
    }

    public function formAdress()
    {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isFORMCODE());
        return $this->tokenstack->push(self::FORMADRESS, $buf);
    }

    public function tableAdress()
    {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isCODE());
        return $this->tokenstack->push(self::TABLEADRESS, $buf);
    }

    public function rowAdress()
    {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isROWCODE());
        return $this->tokenstack->push(self::ROWADRESS, $buf);
    }

    public function columnAdress()
    {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isCODE());
        return $this->tokenstack->push(self::COLUMNADRESS, $buf);
    }

    public function cellAdress()
    {
        $buf = '';
        if ($this->c == 'Ф') {
            do {
                $buf .= $this->c;
                $this->consume();
            } while ($this->isFORMCODE());
        }
        if ($this->c == 'Т') {
            do {
                $buf .= $this->c;
                $this->consume();
            } while ($this->isCODE());
        }
        if ($this->c == 'С') {
            do {
                $buf .= $this->c;
                $this->consume();
            } while ($this->isROWCODE());
        }
        if ($this->c == 'Г') {
            do {
                $buf .= $this->c;
                $this->consume();
            } while ($this->isCODE());
        }
        return $this->tokenstack->push(self::CELLADRESS, $buf);
    }

    /** WS : (' '|'\t'|'\n'|'\r')* ; // ignore any whitespace */
    public function ws()
    {
        while(ctype_space($this->c)) {
            $this->consume();
        }
    }
}

?>
<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 29.08.2017
 * Time: 16:53
 */

namespace App\Medinfo\DSL;


class ControlFunctionLexer extends Lexer
{
    const LPARENTH      = 2;
    const RPARENTH      = 3;
    const PLUS          = 4;
    const MINUS         = 5;
    const MULTIPLY      = 6;
    const DIVIDE        = 7;
    const NUMBER        = 8;
    const CELLADRESS    = 9;
    const COMMA         = 10;
    const BOOLEAN       = 11;
    const COLON         = 12;
    const EXCLAMATION   = 13;
    const NAME          = 14;
    // Следующие типы для парсера
    const ARG           = 15;
    const GROUP         = 16;
    const UNIT          = 17;
    const CELLRANGE     = 18;

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
        "CELLADRESS",
        "COMMA",
        "BOOLEAN",
        "COLON",
        "EXCLAMATION",
        "NAME",
        "ARG",
        "GROUP",
        "UNIT",
        "CELLRANGE",
    ];
    public $celladressStack;

    public function __construct($input)
    {
        parent::__construct($input);
        $this->celladressStack = new \SplDoublyLinkedList;
    }

    public function getTokenName($x)
    {
        return self::$tokenNames[$x];
    }

    public function getTokenType($x)
    {
        return self::$tokenNames[$x];
    }

    public function getTokenStack()
    {
        $token = $this->nextToken();
        while($token->type !== CalculationFunctionLexer::EOF_TYPE) {
            $token = $this->nextToken();
        }
        return $this->tokenstack;
    }

    public function nextToken()
    {
        while ($this->c != self::EOF) {
            switch ($this->c) {
                case " " :
                case "\t":
                case "\n":
                case "\r":
                    $this->ws();
                    continue;
                case ',' :
                    $this->consume();
                    $token = new Token(self::COMMA, ',');
                    $this->tokenstack->push($token);
                    return $token;
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
                case '=' :
                case '>' :
                case '<' :
                    return $this->boolean_sign();
                case ':' :
                    $this->consume();
                    $token = new Token(self::COLON, ':');
                    $this->tokenstack->push($token);
                    return $token;
                case '!' :
                    $this->consume();
                    $token = new Token(self::EXCLAMATION, '!');
                    $this->tokenstack->push($token);
                    return $token;
                case $this->c >= 'а' && $this->c <= 'я':
                    return $this->name();
                case '.' :
                case $this->c >= '1' && $this->c <= '9':
                    return $this->number();
                case 'Ф':
                case 'Т':
                case 'С':
                case 'Г':
                case 'П':
                    return $this->cellAdress();
                default :
                    throw new \Exception("Неверный символ: " . $this->c);
            }
        }
        $token = new Token(self::EOF_TYPE,"EOF");
        $this->tokenstack->push($token);
        return $token;
    }

    /** WS : (' '|'\t'|'\n'|'\r')* ; // игнорируем все пробелы, табуляции, переносы строк ... */
    public function ws()
    {
        while(ctype_space($this->c)) {
            $this->consume();
        }
    }

    public function boolean_sign()
    {
        $buf = $this->c;
        $this->consume();
        if ($this->c == '=') {
            $buf .= $this->c;
            $this->consume();
        }
        $token = new Token(self::BOOLEAN, $buf);
        $this->tokenstack->push($token);
        return $token;
    }

    public function name()
    {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isFUNCNAME());
        $token = new Token(self::NAME, $buf);
        $this->tokenstack->push($token);
        return $token;
    }

    public function number()
    {
        $buf = '';
        $decimal_separator = false;
        $token_type = self::NUMBER;
        do {
            if ($this->c === '.') {
                if ($decimal_separator) {
                    throw new \Exception("Лишний десятичный разделитель в числе " . $buf);
                } else {
                    $decimal_separator = true;
                }
            }
            $buf .= $this->c;
            $this->consume();
        } while ($this->isNUMBER());
        $token = new Token(self::NUMBER, $buf);
        $this->tokenstack->push($token);
        return $token;
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
        if ($this->c == 'П') {
            do {
                $buf .= $this->c;
                $this->consume();
            } while ($this->isPERIODCODE());
        }
        $this->celladressStack->push($buf);
        $token = new Token(self::CELLADRESS, '%'. ($this->celladressStack->count()-1));
        $this->tokenstack->push($token);
        return $token;
    }

    public function isFUNCNAME()
    {
        return $this->c >= 'а' && $this->c <= 'я';
    }

    public function isNUMBER()
    {
        return $this->c == '.' || ($this->c >= '0' && $this->c <= '9');
    }

    public function isFORMCODE()
    {
        return
            $this->c != 'Т' && (
                ($this->c >= '0' && $this->c <= '9') ||
                ($this->c >= 'а' && $this->c <= 'я') ||
                $this->c == '.' ||
                $this->c == '-'
            );
    }

    public function isCODE()
    {
        return $this->c >= '0' && $this->c <= '9';
    }

    public function isROWCODE()
    {
        return
            $this->c != 'Г' && (
                ($this->c >= '0' && $this->c <= '9') || $this->c == '.'
            );
    }

    public function isPERIODCODE()
    {
        return $this->c == '0' || $this->c == '1';
    }
}
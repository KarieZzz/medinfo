<?php

namespace App\Medinfo\Calculation;

use App\Medinfo\Lexer\ParserException;

class CalculationColumnFunctionParser extends Parser {

    public $functionIndex;

    public function __construct($input) {
        parent::__construct($input);
        $this->tokenNames = CalculationFunctionLexer::$tokenNames;
    }

    public function expression() {
        $r = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
        if ($this->root == null) {
            $this->root = $r;
        }
        $this->currentNode = $r;
        $this->match(CalculationFunctionLexer::EXPRESSION);
        $this->element();
        $this->elements();
        return $this->root;
    }

    public function elements() {
        while ($this->lookahead->type == CalculationFunctionLexer::OPERATOR ) {
            $this->operator();
            $this->element();
        }
    }

    public function operator()
    {
        $r = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
        $this->currentNode->addChild($r);
        $this->match(CalculationFunctionLexer::OPERATOR);

    }

    public function element() {
        $r = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
        $this->currentNode->addChild($r);

        if ($this->lookahead->type == CalculationFunctionLexer::COLUMNADRESS ) {
            $this->match(CalculationFunctionLexer::COLUMNADRESS);
        }
        else if ($this->lookahead->type == CalculationFunctionLexer::OPERAND) {
            $this->match(CalculationFunctionLexer::OPERAND);
        }
        else {
            throw new \Exception("Ожидалось число или адрес ячейки. Получено "  .
                $this->tokenNames[$this->lookahead->type]);
        }
    }

}

?>
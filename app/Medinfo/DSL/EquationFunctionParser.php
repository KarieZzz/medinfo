<?php

namespace App\Medinfo\DSL;

use App\Medinfo\Lexer\ParserException;

class EquationFunctionParser extends Parser {

    public $argStack;

    public function __construct($input) {
        parent::__construct($input);
        $this->argStack = new \SplDoublyLinkedList;
        //$this->tokenNames = ControlFunctionLexer::$tokenNames;
    }

    public function factor()
    {
        // factor: NUMBER | LPARENTH expr RPARENTH
        $node = null;
        if ($this->lookahead->type == ControlFunctionLexer::NUMBER) {
            $node = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->match(ControlFunctionLexer::NUMBER);
        } elseif ($this->lookahead->type == ControlFunctionLexer::LPARENTH) {
            $this->match(ControlFunctionLexer::LPARENTH);
            $node = $this->expression();
            $this->match(ControlFunctionLexer::RPARENTH);
        }
        return $node;
    }

    public function term()
    {
        // term: factor (MULTIPLY | DIVIDE) factor
        $node = null;
        $prev_node = null;
        $leftnode = $this->factor();
        while ($this->lookahead->type == ControlFunctionLexer::MULTIPLY || $this->lookahead->type == ControlFunctionLexer::DIVIDE) {
            $node = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            if(!is_null($prev_node)) {
                $leftnode = $prev_node;
            }
            if ($this->lookahead->type == ControlFunctionLexer::MULTIPLY) {
                $this->match(ControlFunctionLexer::MULTIPLY);
            } elseif ($this->lookahead->type == ControlFunctionLexer::DIVIDE) {
                $this->match(ControlFunctionLexer::DIVIDE);
            }
            if ($leftnode == null) {
                throw new \Exception("Синтаксическая ошибка. Слева от оператора '*' или '/' должно быть число или арифметическое выражение. Узел: " . $node );
            }
            $node->addLeft($leftnode);
            if (is_null($rightnode = $this->factor()) && !is_null($node)) {
                throw new \Exception("Синтаксическая ошибка. Справа от оператора '*' или '/' должно быть число или арифметическое выражение. Узел: " . $node );
            }
            $node->addRight($rightnode);
            $prev_node = $node;
        }
        if (is_null($node)) {
            return $leftnode;
        } elseif(!is_null($node))  {
            return $node;
        } else {
            throw new \Exception("Синтаксическая ошибка");
        }
    }

    public function expression() {
        //expr   : term ((PLUS | MINUS) term)*
        //term   : factor ((MUL | DIV) factor)*
        //factor : INTEGER | LPAREN expr RPAREN | COLUMNADRESS
        $node = null;
        $prev_node = null;
        $leftnode = $this->term();
        while ($this->lookahead->type == ControlFunctionLexer::PLUS || $this->lookahead->type == ControlFunctionLexer::MINUS) {
            $node = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            if(!is_null($prev_node)) {
                $leftnode = $prev_node;
            }
            if ($this->lookahead->type == ControlFunctionLexer::PLUS) {
                $this->match(ControlFunctionLexer::PLUS);
            } elseif ($this->lookahead->type == ControlFunctionLexer::MINUS) {
                $this->match(ControlFunctionLexer::MINUS);
            }
            if ($leftnode == null) {
                throw new \Exception("Синтаксическая ошибка. Слева от оператора '+' или '-' должно быть число или арифметическое выражение. Узел: " . $node );
            }
            $node->addLeft($leftnode);
            if (is_null($rightnode = $this->term()) && !is_null($node)) {
                throw new \Exception("Синтаксическая ошибка. Справа от оператора '+' или '-' должно быть число или арифметическое выражение. Узел: " . $node );
            }
            $node->addRight($rightnode);
            $prev_node = $node;
        }
        if (is_null($node)) {
            return $leftnode;
        } else {
            return $node;
        }
    }

    public function equation()
    {
        // eqv : expr BOOLEAN expr
        $node = null;
        $prev_node = null;
        $leftnode = $this->expression();
        while ($this->lookahead->type == ControlFunctionLexer::BOOLEAN) {
            $node = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->match(ControlFunctionLexer::BOOLEAN);
            if ($leftnode == null) {
                throw new \Exception("Синтаксическая ошибка. Слева от оператора сравнения должно быть число или арифметическое выражение. Узел: " . $node );
            }
            $node->addLeft($leftnode);
            if (is_null($rightnode = $this->expression()) && !is_null($node)) {
                throw new \Exception("Синтаксическая ошибка. Справа от оператора сравнения должно быть число или арифметическое выражение. Узел: " . $node );
            }
            $node->addRight($rightnode);
            $prev_node = $node;
        }
        return $node;
    }

}

?>
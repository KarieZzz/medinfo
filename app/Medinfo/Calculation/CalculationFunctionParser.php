<?php

namespace App\Medinfo\Calculation;

use App\Medinfo\Lexer\ParserException;

class CalculationFunctionParser extends Parser {

    public function __construct($input) {
        parent::__construct($input);
        $this->tokenNames = CalculationFunctionLexer::$tokenNames;
    }

    public function factor()
    {
        // factor: NUMBER | LPARENTH expr RPARENTH
        $node = null;
        if ($this->lookahead->type == CalculationFunctionLexer::NUMBER) {
            $node = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->match(CalculationFunctionLexer::NUMBER);
        } elseif ($this->lookahead->type == CalculationFunctionLexer::COLUMNADRESS) {
            $node = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->match(CalculationFunctionLexer::COLUMNADRESS);
        } elseif ($this->lookahead->type == CalculationFunctionLexer::LPARENTH) {
            $this->match(CalculationFunctionLexer::LPARENTH);
            $node = $this->expression();
            $this->match(CalculationFunctionLexer::RPARENTH);
        }
        return $node;
    }

    public function term()
    {
        // term: factor (MULTIPLY | DIVIDE) factor
        $node = null;
        $prev_node = null;
        $leftnode = $this->factor();
        while ($this->lookahead->type == CalculationFunctionLexer::MULTIPLY || $this->lookahead->type == CalculationFunctionLexer::DIVIDE) {
            $node = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            if(!is_null($prev_node)) {
                $leftnode = $prev_node;
            }
            if ($this->lookahead->type == CalculationFunctionLexer::MULTIPLY) {
                $this->match(CalculationFunctionLexer::MULTIPLY);
            } elseif ($this->lookahead->type == CalculationFunctionLexer::DIVIDE) {
                $this->match(CalculationFunctionLexer::DIVIDE);
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
        while ($this->lookahead->type == CalculationFunctionLexer::PLUS || $this->lookahead->type == CalculationFunctionLexer::MINUS) {
            $node = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            if(!is_null($prev_node)) {
                $leftnode = $prev_node;
            }
            if ($this->lookahead->type == CalculationFunctionLexer::PLUS) {
                $this->match(CalculationFunctionLexer::PLUS);
            } elseif ($this->lookahead->type == CalculationFunctionLexer::MINUS) {
                $this->match(CalculationFunctionLexer::MINUS);
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

}

?>
<?php

namespace App\Medinfo\DSL;

class ControlFunctionParser extends Parser {

    public $celladressStack = [];
    public $cellrangeStack = [];
    public $rcStack = [];
    public $rcRangeStack = [];
    public $includeGroupStack = [];
    public $excludeGroupStack = [];
    public $argStack = [];

    public $currentArgIndex;
    const COMPARE       = 1;
    const DEPENDENCY    = 2;
    const INTERANNUAL   = 3;
    const MULTIPLICITY  = 4;
    const SUM           = 5;
    const MIN           = 6;
    const MAX           = 7;
    const GROUPS        = 8;
    const ROWS          = 9;
    const COLUMNS       = 10;

    public static $functionNames = [
        "n/a",
        "сравнение",
        "зависимость",
        "межгодовой",
        "кратность",
        "сумма",
        "меньшее",
        "большее",
        "группы",
        "строки",
        "графы",
    ];

    public function __construct($input) {
        parent::__construct($input);
        //$this->celladressStack = new \SplDoublyLinkedList;
        $this->tokenNames = ControlFunctionLexer::$tokenNames;
    }

    public function factor()
    {
        // factor: NUMBER | CELLADRESS | LPARENTH expr RPARENTH | subfunc
        $node = null;
        if ($this->lookahead->type == ControlFunctionLexer::NUMBER) {
            $node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->argStack[$this->currentArgIndex][] = $node;
            $this->match(ControlFunctionLexer::NUMBER);
        } elseif ($this->lookahead->type == ControlFunctionLexer::CELLADRESS) {
            $node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->argStack[$this->currentArgIndex][] = $node;
            $this->celladressStack[$this->lookahead->text]['node'] = $node;
            $this->match(ControlFunctionLexer::CELLADRESS);
        } elseif ($this->lookahead->type == ControlFunctionLexer::LPARENTH) {
            $this->match(ControlFunctionLexer::LPARENTH);
            $node = $this->expression();
            $this->match(ControlFunctionLexer::RPARENTH);
        } elseif ($this->lookahead->type == ControlFunctionLexer::NAME) {
            // subfunc : NAME LPARENTH ca_range_args RPARENTH
            $node = $this->subfunc();
/*            $node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->match(ControlFunctionLexer::NAME);
            $this->match(ControlFunctionLexer::LPARENTH);
            $this->ca_range_args($node);
            $this->match(ControlFunctionLexer::RPARENTH);*/
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
            $node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->argStack[$this->currentArgIndex][] = $node;
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
            $node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->argStack[$this->currentArgIndex][] = $node;
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

    public function carg()
    {
        // Функция контроля "сравнение" принимает три обязательных аргумента и два опциональных
        // arg_0 : expr
        // arg_1 : expr
        // arg_2 : boolean
        // arg_3 : subfunc | null
        // arg_4 : subfunc | null
        switch ($this->currentArgIndex) {
            case 0 :
            case 1 :
                $arg_node = new ControlFunctionParseTree(ControlFunctionLexer::ARG, 'arg_' . $this->currentArgIndex);
                $exp_node = $this->expression();
                $arg_node->addChild($exp_node);
                $this->currentArgIndex++;
                $this->match(ControlFunctionLexer::COMMA);
                $this->root->addChild($arg_node);
                //dump($this->currentNode);
                //dump($this->currentArgIndex);
                $this->carg();
                break;
            case 2 :
                $arg_node = new ControlFunctionParseTree(ControlFunctionLexer::ARG, 'arg_' . $this->currentArgIndex);
                $bool_node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
                $arg_node->addChild($bool_node);
                $this->currentArgIndex++;
                $this->match(ControlFunctionLexer::BOOLEAN);
                $this->root->addChild($arg_node);
                //dd($this->currentNode);
                //$this->match(ControlFunctionLexer::COMMA);
                $this->carg();
                break;
            case 3 :
            case 4 :
                if ($this->lookahead->type == ControlFunctionLexer::COMMA) {
                    $this->match(ControlFunctionLexer::COMMA);
                    $this->carg();
                } elseif ($this->lookahead->type == ControlFunctionLexer::RPARENTH) {
                    $this->currentArgIndex = 5;
                    $this->carg();
                } else {
                    $arg_node = new ControlFunctionParseTree(ControlFunctionLexer::ARG, 'arg_' . $this->currentArgIndex);
                    $subfunc_node = $this->subfunc();
                    $arg_node->addChild($subfunc_node);
                    $this->currentArgIndex++;
                    $this->root->addChild($arg_node);
                    $this->carg();
                }
                break;
            case 5 :
                if ($this->lookahead->type != ControlFunctionLexer::RPARENTH) {
                    throw new \Exception("
                        Ожидалось " . ControlFunctionLexer::$tokenNames[ControlFunctionLexer::RPARENTH]  . ". Получено " .
                        ControlFunctionLexer::$tokenNames[$this->lookahead->type]
                    );
                }
                break;
        }
    }

    public function cargs()
    {
        // LPARENTH carg* RPARENTH
        // * - arg_1 arg_2  ... arg_n
        if ($this->lookahead->type == ControlFunctionLexer::LPARENTH) {
            $this->match(ControlFunctionLexer::LPARENTH);
            $this->carg();
            $this->match(ControlFunctionLexer::RPARENTH);
        }
    }

    public function ca_range_args($func_node)
    {
        // ca_range_args : CELLADRESS | CELLADRESS : CELLADRESS | ...
        while($this->lookahead->type == ControlFunctionLexer::CELLADRESS) {
            $celladress_left = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $this->match(ControlFunctionLexer::CELLADRESS);
            if ($this->lookahead->type == ControlFunctionLexer::COLON) {
                $this->match(ControlFunctionLexer::COLON);
                $celladress_right = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
                $this->match(ControlFunctionLexer::CELLADRESS);
                $cellrange_key = $celladress_left->content . ' ' .  $celladress_right->content;
                $cellrange = new ControlFunctionParseTree(ControlFunctionLexer::CELLRANGE, $cellrange_key);
                $cellrange->addChild($celladress_left);
                $cellrange->addChild($celladress_right);
                $func_node->addChild($cellrange);
                $this->cellrangeStack[$cellrange_key]['node'] = $cellrange;
                //$this->celladressStack->push($cellrange);
                if ($this->lookahead->type == ControlFunctionLexer::COMMA) {
                    $this->match(ControlFunctionLexer::COMMA);
                }
            } elseif ($this->lookahead->type == ControlFunctionLexer::COMMA) {
                $func_node->addChild($celladress_left);
                $this->celladressStack[$celladress_left->content]['node'] = $celladress_left;
                $this->match(ControlFunctionLexer::COMMA);
            } elseif ($this->lookahead->type == ControlFunctionLexer::RPARENTH) {
                $func_node->addChild($celladress_left);
                $this->celladressStack[$celladress_left->content]['node'] = $celladress_left ;
            }
        }
    }

    public function num_range_args($func_node)
    {
        // num_range_args : MULTIPLY | ((NUMBER | ELCODE) | (NUMBER | ELCODE) - (NUMBER | ELCODE) | ...)
        if ($this->lookahead->type == ControlFunctionLexer::MULTIPLY) {
            $multiply_sign = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
            $func_node->addChild($multiply_sign);
            $this->match(ControlFunctionLexer::MULTIPLY);
            return;
        }
        while($this->lookahead->type == ControlFunctionLexer::NUMBER || $this->lookahead->type == ControlFunctionLexer::ELCODE) {
            $range_left = new ControlFunctionParseTree(ControlFunctionLexer::NUMBER, $this->lookahead->text);
            if ($this->lookahead->type == ControlFunctionLexer::NUMBER) {
                $this->match(ControlFunctionLexer::NUMBER);
            } elseif ($this->lookahead->type == ControlFunctionLexer::ELCODE) {
                $this->match(ControlFunctionLexer::ELCODE);
            }
            if ($this->lookahead->type == ControlFunctionLexer::MINUS) {
                $this->match(ControlFunctionLexer::MINUS);
                $range_right = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
                $this->match(ControlFunctionLexer::NUMBER);
                $range_key = $range_left->content . ' ' .  $range_right->content;
                $range = new ControlFunctionParseTree(ControlFunctionLexer::RCRANGE, $range_key);
                $range->addChild($range_left);
                $range->addChild($range_right);
                $func_node->addChild($range);
                $this->rcRangeStack[$range_key]['node'] = $range;
                if ($this->lookahead->type == ControlFunctionLexer::COMMA) {
                    $this->match(ControlFunctionLexer::COMMA);
                }
            } elseif ($this->lookahead->type == ControlFunctionLexer::COMMA) {
                $func_node->addChild($range_left);
                $this->rcStack[] = $range_left->content;
                $this->match(ControlFunctionLexer::COMMA);
            } elseif ($this->lookahead->type == ControlFunctionLexer::RPARENTH) {
                $func_node->addChild($range_left);
                $this->rcStack[] = $range_left->content;
            }
        }
    }

    public function group_range_args($func_node)
    {
        // group_range_args : null | MULTIPLY | (NAME | !NAME | ...)
        //                                ^ incl  ^ excl
        if ($this->lookahead->type == ControlFunctionLexer::MULTIPLY || $this->lookahead->type == ControlFunctionLexer::RPARENTH) {
            $multiply_sign = new ControlFunctionParseTree(ControlFunctionLexer::MULTIPLY, '*');
            $func_node->addChild($multiply_sign);
            if ($this->lookahead->type == ControlFunctionLexer::MULTIPLY) {
                $this->match(ControlFunctionLexer::MULTIPLY);
            }
            return;
        }
        while($this->lookahead->type == ControlFunctionLexer::NAME || $this->lookahead->type == ControlFunctionLexer::EXCLAMATION) {
            if ($this->lookahead->type == ControlFunctionLexer::NAME) {
                $group_node = new ControlFunctionParseTree(ControlFunctionLexer::INGROUP, $this->lookahead->text);
                $this->match(ControlFunctionLexer::NAME);
                $func_node->addChild($group_node);
                $this->includeGroupStack[] = $group_node->content;
            } elseif($this->lookahead->type == ControlFunctionLexer::EXCLAMATION) {
                $this->match(ControlFunctionLexer::EXCLAMATION);
                $group_node = new ControlFunctionParseTree(ControlFunctionLexer::OUTGROUP, $this->lookahead->text);
                $this->match(ControlFunctionLexer::NAME);
                $func_node->addChild($group_node);
                $this->excludeGroupStack[] = $group_node->content;
            }
            if ($this->lookahead->type == ControlFunctionLexer::COMMA) {
                $this->match(ControlFunctionLexer::COMMA);
            }
        }
    }

    public function subfunc()
    {
        // subfunc : NAME num_range_args
        $func_name = $this->lookahead->text;
        $node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);

        switch (array_search($func_name, self::$functionNames)) {
            case self::SUM :
            case self::MIN :
            case self::MAX :
                $this->ca_range_args($node);
                $this->argStack[$this->currentArgIndex][] = $node;
                break;
            case self::ROWS :
            case self::COLUMNS :
                $this->num_range_args($node);
                break;
            case self::GROUPS :
                $this->group_range_args($node);
                break;
            default :
                throw new \Exception("Функция <$func_name> не существует");
        }
        $this->match(ControlFunctionLexer::RPARENTH);
        return $node;
    }

    public function func()
    {
        // func : NAME cargs
        if ($this->lookahead->type == ControlFunctionLexer::NAME) {
            $func_name = $this->lookahead->text;
            if (!in_array($this->lookahead->text, self::$functionNames)) {
                throw new \Exception("Функция <$func_name> не существует");
            }
            $this->root = new ControlFunctionParseTree($this->lookahead->type, $func_name);
            //$this->root = $this->currentNode;
            $this->currentArgIndex = 0;
            $this->match(ControlFunctionLexer::NAME);
            $this->cargs();
        } else {
            throw new \Exception(
                "Ожидалось объявление функции контроля (сравнение, зависимость ...). Получено " .
                ControlFunctionLexer::$tokenNames[$this->lookahead->type]
            );
        }
    }
}

?>
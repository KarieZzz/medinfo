<?php

namespace App\Medinfo\DSL;

use function PHPSTORM_META\type;

class ControlFunctionParser extends Parser {

    public $celladressStack = [];
    public $cellrangeStack = [];
    public $rcStack = [];
    public $rcRangeStack = [];
    public $includeGroupStack = [];
    public $excludeGroupStack = [];
    public $argStack = [];
    public $currentArgIndex = 0;
    //public $function_name;
    //public $arguments;

    public function __construct($input) {
        parent::__construct($input);
        //$this->argStack = new \SplDoublyLinkedList;
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
            $node = $this->subfunction();
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

    public function boolean() {
        // =, >, <, >=, <=, ^
        //dd($this->lookahead->type);
        if ($this->lookahead->type !== ControlFunctionLexer::BOOLEAN) {
            return null;
        }
        $bool_node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
        //dd($bool_node);
        $this->match(ControlFunctionLexer::BOOLEAN);
        return $bool_node;
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
                //$this->argStack->push($cellrange);
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

    public function subfunction()
    {
        // subfunc : NAME ( ca_range_args | group_range_args | num_range_args )
        if ($this->lookahead->type !== ControlFunctionLexer::NAME) {
            return null;
        }
        $func_name = $this->lookahead->text;

        $node = new ControlFunctionParseTree($this->lookahead->type, $this->lookahead->text);
        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        switch (FunctionDispatcher::functionIndex($func_name)) {
            case FunctionDispatcher::SUM :
            case FunctionDispatcher::MIN :
            case FunctionDispatcher::MAX :
            case FunctionDispatcher::DIAPAZON :
                $this->ca_range_args($node);
                $this->argStack[$this->currentArgIndex][] = $node;
                break;
            case FunctionDispatcher::ROWS :
            case FunctionDispatcher::COLUMNS :
                $this->num_range_args($node);
                break;
            case FunctionDispatcher::GROUPS :
                $this->group_range_args($node);
                break;
            default :
                throw new \Exception("Функция <$func_name> не существует");
        }
        $this->match(ControlFunctionLexer::RPARENTH);
        return $node;
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
                    $subfunc_node = $this->subfunction();
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

        //$no_more_args = false;
        $missing_arg = false;
        $this->match(ControlFunctionLexer::LPARENTH);
        $arguments = new ArgParser(FunctionDispatcher::getProperties($this->function_name));


        $i = 1;
        do {
            $arg_type = $arguments->lookahead->argType;
            $exp_node = $this->$arg_type();
            $arg_node = new ControlFunctionParseTree(ControlFunctionLexer::ARG, 'arg_' . $this->currentArgIndex);
            if (!is_null($exp_node)) {
                $arg_node->addChild($exp_node);
            } elseif ($arguments->lookahead->argRequired) {
                throw  new \Exception("Аргумент {$i} <$arg_type> является обязательным для функции <{$this->function_name}>");
            }
            $this->root->addChild($arg_node);
            $this->currentArgIndex++;
            if ($arguments->last && ($this->lookahead->type !== ControlFunctionLexer::RPARENTH)) {
                throw new \Exception('В данной функции предусмотрено не более ' . $i . ' аргументов');
            }

            if ($this->lookahead->type !== ControlFunctionLexer::RPARENTH) {
                $this->match(ControlFunctionLexer::COMMA);

            }

            //dump($arg_node);
            $i++;

        } while ($arguments->next());
/*        for ($this->currentArgIndex = 0; $this->currentArgIndex < count($this->arguments); $this->currentArgIndex++) {
            $properties =  new ArgProperties($this->arguments[$this->currentArgIndex]);
            $properties->makeStack();
            $arg_type = $properties->argType;
            $exp_node = $this->$arg_type();
            //dump($exp_node);
            $arg_node = new ControlFunctionParseTree(ControlFunctionLexer::ARG, 'arg_' . $this->currentArgIndex);
            if (!is_null($exp_node)) {
                $arg_node->addChild($exp_node);
            } elseif ($properties->argRequired) {
                throw  new \Exception("Аргумент {$i} <$arg_type> является обязательным для функции <{$this->function_name}>");
            }
            $this->root->addChild($arg_node);
            if ($this->lookahead->type === ControlFunctionLexer::RPARENTH) {
                break;
            }
            $this->match(ControlFunctionLexer::COMMA);

        }*/
        //dd($this->root);
        //dd($this->root);
/*        foreach ($this->arguments as $argument) {

            $function_name = $props[0];

            if ($this->lookahead->type == ControlFunctionLexer::COMMA) {
                $missing_arg = true;
            } else {
                $missing_arg = false;
            }
            if (($missing_arg || $no_more_args) && $required) {
                throw new \Exception("Аргумент {$this->currentArgIndex} обязателен для функции {$this->function_name}");
            } elseif (($missing_arg || $no_more_args)) {
                break;
            }
            $exp_node = $this->$function_name();

            if (is_null($exp_node && $required)) {
                throw new \Exception("Аргумент {$this->currentArgIndex} обязателен для функции {$this->function_name}");
            }
            if (!is_null($exp_node)) {
                $arg_node = new ControlFunctionParseTree(ControlFunctionLexer::ARG, 'arg_' . $this->currentArgIndex);
                $arg_node->addChild($exp_node);
                $this->root->addChild($arg_node);
                $this->currentArgIndex++;
            }

            if ($this->lookahead->type == ControlFunctionLexer::COMMA) {
                $this->match(ControlFunctionLexer::COMMA);
            } elseif ($this->lookahead->type == ControlFunctionLexer::RPARENTH) {
                $no_more_args = true;
            } else {
                throw new \Exception("Ожидалось завершение функции <)> Получено " . ControlFunctionLexer::$tokenNames[$this->lookahead->type]);
            }

            if ($this->currentArgIndex === 4) {
                dd($missing_arg);
            }
        }*/
        //$this->carg();
        $this->match(ControlFunctionLexer::RPARENTH);

    }

    public function func()
    {
        // func : NAME cargs
        if ($this->lookahead->type == ControlFunctionLexer::NAME) {
            if (array_key_exists($this->lookahead->text, FunctionDispatcher::$functionNames)) {
                $this->function_name = $this->lookahead->text;
                $this->function_index = FunctionDispatcher::$functionNames[$this->lookahead->text];
            } else {
                throw new \Exception("Функция <$this->function_name> не существует");
            }
            $this->root = new ControlFunctionParseTree($this->lookahead->type, $this->function_name);
            //$this->root = $this->currentNode;
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
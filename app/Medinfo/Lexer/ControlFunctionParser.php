<?php

namespace App\Medinfo\Lexer;

use App\Medinfo\Lexer\ParserException;

class ControlFunctionParser extends Parser {

    public $functionIndex;

    public function __construct(Lexer $input) {
        parent::__construct($input);
    }

    public function run()
    {
        if ($this->lookahead->type == ControlFunctionLexer::NAME ) {
            $this->functionIndex = FunctionDispatcher::functionIndex($this->lookahead->text);
            $functionName = FunctionDispatcher::$structNames[$this->functionIndex];
            return $this->$functionName();
        } else {
            throw new ParserException("Ожидалось объявление функции. Обнаружено " .  $this->input->getTokenName($this->lookahead->type));
        }
    }

    public function compare() {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode; // сохраняем текущий узел, что бы вернутся к нему в конце функции
        if ($this->root == null) {
            $this->root = $r;
        } else {
            $this->currentNode->addChild($r);
        }
        $this->currentNode = $r;
        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        $this->expression(); // Первый аргумент - вычисление
        $this->match(ControlFunctionLexer::COMMA);
        $this->expression(); // Второй аргумент - вычисление
        $this->match(ControlFunctionLexer::COMMA);
        $this->compare_action();
        $this->match(ControlFunctionLexer::COMMA);
        $this->scope(); // Четвертый аргумент - функции ограничения применения данного контроля к группе учреждений
        $this->match(ControlFunctionLexer::COMMA);
        $this->iterations(); // Пятый аргумент - функции итерации по строкам и графам
        $this->match(ControlFunctionLexer::RPARENTH);
        $this->currentNode = $o;
        return $this->root;
    }

    public function dependency() {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode; // сохраняем текущий узел, что бы вернутся к нему в конце функции
        if ($this->root == null) {
            $this->root = $r;
        } else {
            $this->currentNode->addChild($r);
        }
        $this->currentNode = $r;
        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        $this->expression(); // Первый аргумент - вычисление
        $this->match(ControlFunctionLexer::COMMA);
        $this->expression(); // Второй аргумент - вычисление
        $this->match(ControlFunctionLexer::COMMA);
        $this->scope(); // Четвертый аргумент - функции ограничения применения данного контроля к группе учреждений
        $this->match(ControlFunctionLexer::COMMA);
        $this->iterations(); // Пятый аргумент - функции итерации по строкам и графам
        $this->match(ControlFunctionLexer::RPARENTH);
        $this->currentNode = $o;
        return $this->root;
    }
    
    public function interannual() {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode; // сохраняем текущий узел, что бы вернутся к нему в конце функции
        if ($this->root == null) {
            $this->root = $r;
        } else {
            $this->currentNode->addChild($r);
        }
        $this->currentNode = $r;
        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        if ($this->lookahead->type == ControlFunctionLexer::NAME ) {
            $this->diapason(); // Первый аргумент - диапазон ячеек
        } elseif ($this->lookahead->type == ControlFunctionLexer::CELLADRESS) {
            //$this->celladress(); // либо адрес ячейки текущего документа
            $this->expression(); // либо выражение
        }
        $this->match(ControlFunctionLexer::COMMA);
        if ($this->lookahead->type == ControlFunctionLexer::NUMBER ) {
            $this->threshold(); // Второй аргумент - пороговое значение отклонения
        } elseif ($this->lookahead->type == ControlFunctionLexer::CELLADRESS) {
            //$this->celladress(); //  либо адрес ячейки прошлогоднего документа
            $this->expression(); //  либо выражение
        }
        //dd($this);
        if ($this->lookahead->type == ControlFunctionLexer::COMMA ) {
            $this->match(ControlFunctionLexer::COMMA);
            $this->threshold(); // Третий аргумент - пороговое значение отклонения
        }
        $this->match(ControlFunctionLexer::RPARENTH);
        $this->currentNode = $o;
        return $this->root;
    }

    public function fold() {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode; // сохраняем текущий узел, что бы вернутся к нему в конце функции
        if ($this->root == null) {
            $this->root = $r;
        } else {
            $this->currentNode->addChild($r);
        }
        $this->currentNode = $r;
        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        $this->diapason(); // Первый аргумент - диапазон проверяемых ячеек
        $this->match(ControlFunctionLexer::COMMA);
        $this->number(); // Второй аргумент - делитель для проверки кратности
        $this->match(ControlFunctionLexer::RPARENTH);
        $this->currentNode = $o;
        return $this->root;
    }

    // Первый аргумент в функции. В выражении может несколько элементов, разделенных (пока) знаком плюс или минус
    public function expression() {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        if ($this->lookahead->type == ControlFunctionLexer::OPERATOR) {
            $this->operator();
        }
        $this->element();
        while ($this->lookahead->type == ControlFunctionLexer::OPERATOR ) {
            $this->operator();
            $this->element();
        }

        $this->currentNode = $o;
    }
    // элемент в выражении - или адрес ячейки, или функция "сумма"
    public function element() {
        //$r = new ControlFunctionParseTree(__FUNCTION__);
        //$o = $this->currentNode;
        //$this->currentNode->addChild($r);
        //$this->currentNode = $r;
        if ($this->lookahead->type == ControlFunctionLexer::NUMBER ) {
            $this->number();
        } elseif ($this->lookahead->type == ControlFunctionLexer::CELLADRESS ) {
            $this->celladress();
        } elseif ($this->lookahead->type == ControlFunctionLexer::NAME && $this->lookahead->text == 'сумма') {
            $this->summfunction();
        } elseif ($this->lookahead->type == ControlFunctionLexer::NAME && $this->lookahead->text == 'меньшее') {
            $this->minmaxfunctions();
        } else {
            throw new ParserException("Ожидалось число, адрес ячейки или функция для расчета. Найдено: " . $this->lookahead, 1);
        }

        //$this->currentNode = $o;
    }

    public function operator()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::OPERATOR); // Пока предусмотрено только сложение и вычитание

        $this->currentNode = $o;
    }

    public function number()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::NUMBER);

        $this->currentNode = $o;
    }

    public function threshold()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::NUMBER);

        $this->currentNode = $o;
    }


    public function celladress()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::CELLADRESS);
/*        $this->match(ControlFunctionLexer::FORMADRESS);
        $this->match(ControlFunctionLexer::TABLEADRESS);
        $this->match(ControlFunctionLexer::ROWADRESS);
        $this->match(ControlFunctionLexer::COLUMNADRESS);*/

        $this->currentNode = $o;
    }

    public function cellrange()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->celladress();
        $this->match(ControlFunctionLexer::COLON);
        $this->celladress();

        $this->currentNode = $o;
    }

    public function cellarray()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        if ($this->lookahead->type == ControlFunctionLexer::MULTIPLY ) {
            $this->all();
        } else {
            while ($this->lookahead->type == ControlFunctionLexer::CELLADRESS) {
                $this->cellarray_element();
            }
        }

        $this->currentNode = $o;
    }

    public function summfunction()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        $this->cellarray();
        $this->match(ControlFunctionLexer::RPARENTH);

        $this->currentNode = $o;
    }

    public function diapason()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        $this->cellarray();
        $this->match(ControlFunctionLexer::RPARENTH);

        $this->currentNode = $o;
    }

    public function minmaxfunctions()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        $this->cellarray();
        $this->match(ControlFunctionLexer::RPARENTH);

        $this->currentNode = $o;
    }


    public function compare_action()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::BOOLEAN);

        $this->currentNode = $o;
    }

    public function scope()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        $this->grouparray();
        $this->match(ControlFunctionLexer::RPARENTH);

        $this->currentNode = $o;
    }

    public function grouparray()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        if ($this->lookahead->type == ControlFunctionLexer::MULTIPLY ) {
            $this->all();
        } else {
            while ($this->lookahead->type == ControlFunctionLexer::NAME || $this->lookahead->type == ControlFunctionLexer::EXCLAMATION) {
                $this->groupelement();
                //$this->match(ControlFunctionLexer::NAME);
                if ($this->lookahead->type !== ControlFunctionLexer::RPARENTH) {
                    $this->match(ControlFunctionLexer::COMMA);
                }
            }
        }

        $this->currentNode = $o;
    }

    public function groupelement() {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        if ($this->lookahead->type == ControlFunctionLexer::EXCLAMATION ) {
            $this->currentNode->rule = 'excludedgroup';
            $this->match(ControlFunctionLexer::EXCLAMATION);
            $this->match(ControlFunctionLexer::NAME);
        } elseif ($this->lookahead->type == ControlFunctionLexer::NAME ) {
            $this->currentNode->rule = 'includedgroup';
            $this->match(ControlFunctionLexer::NAME);
        } else {
            throw new ParserException("Ожидалось имя группы, восклицательный знак. Найдено: " . $this->lookahead, 1);
        }

        $this->currentNode = $o;
    }

    public function iterations()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::NAME);
        $this->match(ControlFunctionLexer::LPARENTH);
        $this->iteration_ranges();
        $this->match(ControlFunctionLexer::RPARENTH);

        $this->currentNode = $o;
    }

    public function iteration_ranges()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        if ($this->lookahead->type == ControlFunctionLexer::MULTIPLY ) {
            $this->all();
        } elseif ($this->lookahead->type == ControlFunctionLexer::NUMBER) {
            $this->iteration_range();
        }
/*        else {
            throw new ParserException("Ожидался знак '*', число или диапазон чисел. Найдено: " . $this->lookahead, 1 );
        }*/

        $this->currentNode = $o;
    }

    public function all()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->match(ControlFunctionLexer::MULTIPLY);

        $this->currentNode = $o;
    }

    public function iteration_range()
    {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;
        $this->currentNode->rule = 'iteration_number';
        $this->match(ControlFunctionLexer::NUMBER);

        if ($this->lookahead->type == ControlFunctionLexer::COMMA ) {
            $this->match(ControlFunctionLexer::COMMA);
            $this->currentNode = $o;
            $this->iteration_range();
        } elseif ($this->lookahead->type == ControlFunctionLexer::OPERATOR && $this->lookahead->text = '-') {
            $this->currentNode->rule = 'iteration_range';
            $this->match(ControlFunctionLexer::OPERATOR);
            $this->match(ControlFunctionLexer::NUMBER);
            if ($this->lookahead->type == ControlFunctionLexer::COMMA ) {
                $this->match(ControlFunctionLexer::COMMA);
                $this->currentNode = $o;
                $this->iteration_range();
            }
        }
        /*else {
            throw new ParserException("Ожидалось число или диапазон чисел Найдено: " . $this->lookahead, 1 );
        }*/
    }

    public function cellarray_element() {
        $r = new ControlFunctionParseTree(__FUNCTION__);
        $o = $this->currentNode;
        $this->currentNode->addChild($r);
        $this->currentNode = $r;

        $this->currentNode->rule = 'celladress';
        $this->match(ControlFunctionLexer::CELLADRESS);
        if ($this->lookahead->type == ControlFunctionLexer::COLON) {
            $this->currentNode->rule = 'cellrange';
            $this->match(ControlFunctionLexer::COLON);
            $this->match(ControlFunctionLexer::CELLADRESS);
        }
        if ($this->lookahead->type !== ControlFunctionLexer::RPARENTH) {
            $this->match(ControlFunctionLexer::COMMA);
        }

        $this->currentNode = $o;
    }
}

?>
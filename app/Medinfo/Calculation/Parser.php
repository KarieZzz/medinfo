<?php

namespace App\Medinfo\Calculation;

use SplDoublyLinkedList;

abstract class Parser {
    public $input;     // tokenstack в формате двунаправленного списка
    public $lookahead; // Следующий обрабатываемый токен
    public $root; // ParseTree root node
    public $currentNode; // ParseTree current node
    protected $tokenNames; // Для читабельного вывода исключений

    public function __construct(SplDoublyLinkedList $input) {
        $input->rewind();
        $this->input = $input;
        $this->consume();
    }
    
    public function match($x) {
        if ($this->lookahead->type == $x ) {
              $this->consume();
        } else {
            throw new \Exception("Ошибка разбора правила контроля. Ожидался токен <" . $this->tokenNames[$this->input->current()->type]
                . ">: Обнаружен <" . $this->tokenNames[$this->input->current()->type] . ">");
        }
    }

    public function consume() {
        $this->lookahead = $this->input->current();
        $this->input->next();
    }
}

?>
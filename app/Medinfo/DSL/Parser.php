<?php

namespace App\Medinfo\DSL;

use SplDoublyLinkedList;

abstract class Parser {
    protected $input;           // tokenstack в формате двунаправленного списка
    protected $lookahead;       // Следующий обрабатываемый токен
    public $root;               // ParseTree root node
    protected $rootOffset;      // Позиция токена, примаемого за вершину дерева
    protected $currentNode;     // ParseTree текущий узед
    protected $prevNode;        // ParseTree предыдущий узед
    protected $tokenNames;      // Для читабельного вывода исключений
    public $function_name;      // Имя обрабатываемой функции для последующего использования
    public $function_index;     // Индекс обрабатываемой функции для последующего использования

    public function __construct(SplDoublyLinkedList $input) {
        $input->rewind();
        $this->input = $input;
        $this->consume();
    }
    
    public function match($x) {
        if ($this->lookahead->type === $x ) {
              $this->consume();
        } else {
            throw new \Exception("Ошибка разбора правила контроля. Ожидался токен <" . $this->tokenNames[$this->input->current()->type]
                . ">: Обнаружен <" . $this->tokenNames[$this->lookahead->type] . ">");
        }
    }

    public function consume() {
        $this->lookahead = $this->input->current();
        $this->input->next();
    }
}

?>
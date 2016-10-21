<?php

namespace App\Medinfo\Lexer;

abstract class Parser {
    public $input;     // from where do we get tokens?
    public $lookahead; // the current lookahead token
    public $root; // ParseTree root node
    public $currentNode; // ParseTree current node

    public function __construct(Lexer $input) {
        $this->input = $input;
        $this->consume();
    }
    
    /** If lookahead token type matches x, consume & return else error */
    public function match($x) {
        // добавляю прямо в абстрактный класс, так думаю, что достаточно паттерна ParseTree
        $this->currentNode->addToken($this->lookahead);
        if ($this->lookahead->type == $x ) {
        //if ($this->lookahead['type'] == $x ) {
            $this->consume();
        } else {
            throw new \Exception("Ошибка разбора правила контроля. Ожидался токен <" . $this->input->getTokenName($x) . ">: Обнаружен <" . $this->input->getTokenName($this->lookahead->type) . ">");
            //dd("Expecting token " . $this->input->getTokenName($x) . ":Found " . $this->lookahead);
        }
    }

    public function consume() {
        $this->lookahead = $this->input->nextToken();
    }
}

?>
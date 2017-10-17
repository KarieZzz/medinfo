<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 17.10.2017
 * Time: 10:18
 */

namespace App\Medinfo\DSL;


class ArgParser
{
    protected $input;          // массив со свойствами аргумента обрабатываемой функции
    protected $current = 0;
    public $lookahead;      // Следующий обрабатываемый аргумент
    public $last = false;   // Последний ли аргумент?

    public function __construct(Array $input) {
        $this->input = $input;
        if (count($this->input) === 0) {
            throw new \Exception('В описании функции не указано ни одного аргумента');
        }
        $this->lookahead = new ArgProperties($this->input[$this->current]);
    }

    public function match($x) {
        if ($this->lookahead->argType === $x ) {
            $this->next();
        } else {
            throw new \Exception("Ошибка разбора аргументов функции. Ожидалось свойство <" . $x
                . ">: Обнаружено <" . $this->lookahead->argType . ">");
        }
    }

    public function next() {
        $this->current++;
        if ($this->current >= count($this->input)) {
            return ArgProperties::EOP;
        }
        else if($this->current == count($this->input)-1) {
            $this->lookahead = new ArgProperties($this->input[$this->current]);
            $this->last = true;
            return $this->lookahead;
        } else {
            $this->lookahead = new ArgProperties($this->input[$this->current]);
            return $this->lookahead;
        }
    }

}
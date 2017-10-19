<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 17.10.2017
 * Time: 10:26
 */

namespace App\Medinfo\DSL;


class ArgProperties
{
    const EOP = null;
    protected $input;
    protected $position = 0;
    //protected $property;
    protected $propstack;
    public $argType; // Первое свойство условно принимаем как Тип аргумента
    public $argRequired = false;

    const EXPRESSION    = 2;
    const SUBFUNCTION   = 3;
    const BOOLEAN       = 4;
    const FACTOR        = 5;
    const FLOAT         = 6;
    const DIAPAZON      = 7;
    const REQUIRED      = 8;
    const GROUPS        = 9;
    const ROWS          = 10;
    const COLUMNS       = 11;

    public static $propNames = [
        "n/a",
        "EOP",
        "expression",
        "subfunction",
        "boolean",
        "factor",
        "float",
        "diapazon",
        "required",
        "группы",
        "строки",
        "графы",
    ];

    public function __construct($input) {
        $this->input =  explode('|', $input);
        //$this->property = $this->input[0];
        $this->argType = $this->input[0];
        $this->makeStack();
    }

/*    public function consume() {
        $this->position++;
        if ($this->position >= count($this->input)) {
            $this->property = self::EOP;
        }
        else {
            $this->property = $this->input[$this->position];
        }
    }

    public function match($x) {
        if ( $this->property == $x) {
            $this->consume();
        } else {
            throw new \Exception("Ожидалось свойство аргумента " . $x . "; получено " . $this->property );
        }
    }*/

    public function makeStack()
    {
        foreach ($this->input as $prop) {
            switch ($prop) {
                case 'expression' :
                    $this->propstack[] = self::EXPRESSION;
                    break;
                case 'subfunction' :
                    $this->propstack[] = self::SUBFUNCTION;
                    break;
                case 'boolean' :
                    $this->propstack[] = self::BOOLEAN;
                    break;
                case 'factor' :
                    $this->propstack[] = self::FACTOR;
                    break;
                case 'float' :
                    $this->propstack[] = self::FLOAT;
                    break;
                case 'diapazon' :
                    $this->propstack[] = self::DIAPAZON;
                    break;
                case 'required' :
                    $this->argRequired = true;
                    $this->propstack[] = self::REQUIRED;
                    break;
                case 'группы' :
                    $this->propstack[] = self::GROUPS;
                    break;
                case 'строки' :
                    $this->propstack[] = self::ROWS;
                    break;
                case 'графы' :
                    $this->propstack[] = self::COLUMNS;
                    break;
                default :
                    throw new \Exception("Неизвестное ствойство аргумента: " . $prop);

            }
        }
    }
}
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
    const INTEGER       = 5;
    const FLOAT         = 6;
    const DIAPASON      = 7;
    const REQUIRED      = 8;
    const GROUPS        = 9;
    const GROUP_RANGE   = 10;
    const ROWS          = 11;
    const COLUMNS       = 12;
    const NUM_RANGE     = 13;

    public static $propNames = [
        "n/a",
        "EOP",
        "expression",
        "subfunction",
        "boolean",
        "integer",
        "float",
        "diapason",
        "required",
        "groups",
        "group_range",
        "rows",
        "columns",
        "num_range",
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
                case 'integer' :
                    $this->propstack[] = self::INTEGER;
                    break;
                case 'float' :
                    $this->propstack[] = self::FLOAT;
                    break;
                case 'diapason' :
                    $this->propstack[] = self::DIAPASON;
                    break;
                case 'required' :
                    $this->argRequired = true;
                    $this->propstack[] = self::REQUIRED;
                    break;
                case 'groups' :
                    $this->propstack[] = self::GROUPS;
                    break;
                case 'group_range' :
                    $this->propstack[] = self::GROUP_RANGE;
                    break;
                case 'rows' :
                    $this->propstack[] = self::ROWS;
                    break;
                case 'columns' :
                    $this->propstack[] = self::COLUMNS;
                    break;
                case 'num_range' :
                    $this->propstack[] = self::NUM_RANGE;
                    break;
                default :
                    throw new \Exception("Неизвестное ствойство аргумента: " . $prop);

            }
        }
    }
}
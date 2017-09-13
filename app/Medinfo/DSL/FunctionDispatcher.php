<?php

namespace App\Medinfo\DSL;


class FunctionDispatcher
{
    const COMPARE       = 1;
    const DEPENDENCY    = 2;
    const PRESENCE      = 3;
    const ABSENS        = 4;
    const FOLD          = 5;
    const INTERANNUAL   = 6;

    const INTERPRETERNS = 'App\\Medinfo\\Lexer\\';

    public static $functionNames = [
        "н/а" => null,
        "сравнение" => self::COMPARE,
        "зависимость" => self::DEPENDENCY,
        "наличие" => self::PRESENCE ,
        "отсутствие" => self::ABSENS,
        "кратность" => self::FOLD,
        "межгодовой" => self::INTERANNUAL,
    ];

    public static $structNames = [
        null,
        "compare",
        "dependency",
        "presens",
        "absence",
        "fold",
        "interannual",
    ];

    public static $interpreterNames = [
        null,
        "CompareControlInterpreter",
        "DependencyControlInterpreter",
        "Presens",
        "Absence",
        "FoldControlInterpreter",
        "InterannualControlInterpreter",
    ];

    public static function functionIndex($function_name)
    {
        if(array_key_exists($function_name, self::$functionNames)) {
            return self::$functionNames[$function_name];
        }
        throw new \Exception("Неизвестная функция контроля");
/*        switch ($function_name) {
            case 'сравнение':
            case 'зависимость':
            case 'наличе':
            case 'отсутствие':
            case 'кратность':
            case 'межгодовой':
                return self::$functionNames[$function_name];
            default :
                throw new \Exception("Неизвестная функция контроля");
        }*/
    }
}
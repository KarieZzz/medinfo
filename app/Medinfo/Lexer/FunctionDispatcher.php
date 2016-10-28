<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 27.10.2016
 * Time: 21:04
 */

namespace App\Medinfo\Lexer;


class FunctionDispatcher
{
    const COMPARE   = 1;
    const PRESENCE  = 2;
    const ABSENS    = 3;
    const CEIL      = 4;

    public static $functionNames = [
        "н/а" => null,
        "сравнение" => self::COMPARE,
        "наличие" => self::PRESENCE ,
        "отсутствие" => self::ABSENS,
        "кратность" => self::CEIL,
    ];

    public static $structNames = [
        null,
        "compare",
        "presens",
        "absence",
        "ceil",
    ];

    public static $interpreterNames = [
        null,
        "CompareControlInterpreter",
        "Presens",
        "Absence",
        "Ceil",
    ];

    public static function functionIndex($function_name)
    {
        switch ($function_name) {
            case 'сравнение':
            case 'наличе':
            case 'отсутствие':
            case 'кратность':
                return self::$functionNames[$function_name];
            default :
                throw new \Exception("Неизвестная функция контроля");
        }
    }



}
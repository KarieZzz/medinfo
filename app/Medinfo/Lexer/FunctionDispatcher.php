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
    const DEPENDENCY = 2;
    const PRESENCE  = 3;
    const ABSENS    = 4;
    const CEIL      = 5;

    const INTERPRETERNS = 'App\\Medinfo\\Lexer\\';

    public static $functionNames = [
        "н/а" => null,
        "сравнение" => self::COMPARE,
        "зависимость" => self::DEPENDENCY,
        "наличие" => self::PRESENCE ,
        "отсутствие" => self::ABSENS,
        "кратность" => self::CEIL,
    ];

    public static $structNames = [
        null,
        "compare",
        "dependency",
        "presens",
        "absence",
        "ceil",
    ];

    public static $interpreterNames = [
        null,
        "CompareControlInterpreter",
        "DependencyControlInterpreter",
        "Presens",
        "Absence",
        "Ceil",
    ];

    public static function functionIndex($function_name)
    {
        switch ($function_name) {
            case 'сравнение':
            case 'зависимость':
            case 'наличе':
            case 'отсутствие':
            case 'кратность':
                return self::$functionNames[$function_name];
            default :
                throw new \Exception("Неизвестная функция контроля");
        }
    }



}
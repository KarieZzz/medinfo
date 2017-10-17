<?php

namespace App\Medinfo\DSL;


class FunctionDispatcher
{
    const COMPARE       = 1;
    const DEPENDENCY    = 2;
    const INTERANNUAL   = 3;
    const MULTIPLICITY  = 4;
    const PRESENCE      = 5;
    const ABSENS        = 6;
    const SUM           = 7;
    const MIN           = 8;
    const MAX           = 9;
    const DIAPAZON      = 10;
    const GROUPS        = 11;
    const ROWS          = 12;
    const COLUMNS       = 13;

    const DSL = 'App\\Medinfo\\DSL\\';

    public static $functionNames = [
        "н/а",
        "сравнение"     => self::COMPARE,
        "зависимость"   => self::DEPENDENCY,
        "межгодовой"    => self::INTERANNUAL,
        "кратность"     => self::MULTIPLICITY,
        "наличие"       => self::PRESENCE ,
        "отсутствие"    => self::ABSENS,
        "сумма"         => self::SUM,
        "меньшее"       => self::MIN,
        "большее"       => self::MAX,
        "диапазон"      => self::DIAPAZON,
        "группы"        => self::GROUPS,
        "строки"        => self::ROWS,
        "графы"         => self::COLUMNS,
    ];

    public static $functionIndexes = [
        "н/а",
        "сравнение",
        "зависимость",
        "межгодовой",
        "кратность",
        "наличие",
        "отсутствие",
        "сумма",
        "меньшее",
        "большее",
        "диапазон",
        "группы",
        "строки",
        "графы",
    ];

    public static $structNames = [
        null,
        "compare",
        "dependency",
        "interannual",
        "multiplicity",
        "presens",
        "absence",
    ];

    public static $interpreterNames = [
        null,
        "CompareEvaluator",
        "DependencyEvaluator",
        "InterannualEvaluator",
        "MultiplicityEvaluator",
    ];

    public static $functionArgs = [
        null,
        'сравнение'     => ['expression|required', 'expression|required','boolean|required', 'subfunction|groups|group_range', 'subfunction|rows|columns|num_range'],
        'зависимость'   => ['expression:required|expression:required|subfunction:группы|subfunction:строки,графы'],
        'межгодовой'    => ['diapason|required', 'integer|required'],
        'кратность'     => ['diapason|required', 'float|required'],
        "наличие"       => self::PRESENCE ,
        "отсутствие"    => self::ABSENS,
        "сумма"         => self::SUM,
        "меньшее"       => self::MIN,
        "большее"       => self::MAX,
        "диапазон"      => self::DIAPAZON,
        "группы"        => self::GROUPS,
        "строки"        => self::ROWS,
        "графы"         => self::COLUMNS,
    ];

    public static function getProperties($fname)
    {
        if (array_key_exists($fname, self::$functionArgs)) {
            return self::$functionArgs[$fname];
        }
        throw new \Exception('Описание параметров функции ' . $fname . ' не найдено');
    }

    public static function functionIndex($function_name)
    {
        if(array_key_exists($function_name, self::$functionNames)) {
            //dd(self::$functionNames[$function_name]);
            return self::$functionNames[$function_name];
        }
        //dump($function_name);
        throw new \Exception("Неизвестная функция/подфункция контроля");
    }

}
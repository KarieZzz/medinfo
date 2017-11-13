<?php

namespace App\Medinfo\DSL;


class FunctionDispatcher
{
    const COMPARE       = 1;
    const DEPENDENCY    = 2;
    const INTERANNUAL   = 3;
    const IADIAPAZON    = 4;
    const MULTIPLICITY  = 5;
    const PRESENCE      = 6;
    const ABSENS        = 7;
    const SUM           = 8;
    const MIN           = 9;
    const MAX           = 10;
    const DIAPAZON      = 11;
    const GROUPS        = 12;
    const ROWS          = 13;
    const COLUMNS       = 14;

    const DSL = 'App\\Medinfo\\DSL\\';

    public static $functionNames = [
        "н/а",
        "сравнение"     => self::COMPARE,
        "зависимость"   => self::DEPENDENCY,
        "межгодовой"    => self::INTERANNUAL,
        "мгдиапазон"    => self::IADIAPAZON,
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
        "мгдиапазон",
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

    public static $translators = [
        null,
        "CompareTranslator",
        "DependencyTranslator",
        "InterannualTranslator",
        "IAdiapazonTranslator",
        "MultiplicityTranslator",
    ];

    public static $evaluators = [
        null,
        "CompareEvaluator",
        "DependencyEvaluator",
        "InterannualEvaluator",
        "IAdiapazonEvaluator",
        "MultiplicityEvaluator",
    ];

/*    public static $interpreterNames = [
        null,
        "CompareEvaluator",
        "DependencyEvaluator",
        "InterannualEvaluator",
        "MultiplicityEvaluator",
    ];*/

    public static $functionArgs = [
        null,
        'сравнение'     => ['expression|required', 'expression|required','boolean|required', 'subfunction|группы', 'subfunction|строки|графы|iterator'],
        'зависимость'   => ['expression|required','expression|required','subfunction|группы','subfunction|строки|графы|iterator'],
        'межгодовой'    => ['expression|required|thisyear', 'expression|required|prevyear','factor|required'],
        'мгдиапазон'    => ['subfunction|required|diapazon|iterator', 'factor|required'],
        'кратность'     => ['subfunction|required|diapazon|iterator', 'factor|required'],
    ];

    public static $algorithms = [
        null,
        'сравнение'     => 'a1 a3 a2',
        'зависимость'   => 'a1 ^ a2',
        'межгодовой'    => '(a2 - a1)/a2 * 100 > a3',
        'мгдиапазон'    => '(a1p - a1c)/a1p * 100 > a2',
        'кратность'     => 'a1 % a2 == 0',
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
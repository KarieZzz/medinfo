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
    const UNITCOUNT     = 15;
    const UNITLIST      = 16;
    const CALCULATION   = 17;
    const VALUECOUNT    = 18;
    const IPDIAPAZON    = 19;
    const SECTION       = 20;

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
        "счетмо"        => self::UNITCOUNT,
        "список"        => self::UNITLIST,
        "расчет"        => self::CALCULATION,
        "счетзнач"      => self::VALUECOUNT,
        "мпдиапазон"    => self::IPDIAPAZON,
        "разрез"        => self::SECTION,
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
        "счетмо",
        "список",
        "расчет",
        "счетзнач",
        "мпдиапазон",
        "разрез",
    ];

    public static $translators = [
        null,
        1  => "CompareTranslator",
        2  => "DependencyTranslator",
        3  => "InterannualTranslator",
        4  => "IAdiapazonTranslator",
        5  => "MultiplicityTranslator",
        15 => "UnitCountTranslator",
        17 => "CalculationTranslator",
        18 => "ValueCountTranslator",
        19 => "IPdiapazonTranslator",
        20 => "SectionTranslator",
    ];

    public static $evaluators = [
        null,
        1  => "CompareEvaluator",
        2  => "DependencyEvaluator",
        3  => "InterannualEvaluator",
        4  => "IAdiapazonEvaluator",
        5  => "MultiplicityEvaluator",
        15 => "UnitCountEvaluator",
        17 => "CalculationEvaluator",
        18 => "ValueCountEvaluator",
        19 => "IPdiapazonEvaluator",
        20 => "SectionEvaluator",
    ];

    public static $functionArgs = [
        null,
        'сравнение'     => ['expression|required', 'expression|required','boolean|required', 'subfunction|группы', 'subfunction|строки|графы|iterator'],
        'зависимость'   => ['expression|required','expression|required','subfunction|группы','subfunction|строки|графы|iterator'],
        'межгодовой'    => ['expression|required|thisyear', 'expression|required|prevyear','factor|required'],
        'мгдиапазон'    => ['subfunction|required|diapazon|iterator', 'factor|required'],
        'кратность'     => ['subfunction|required|diapazon|iterator', 'factor|required'],
        //'счетмо'        => ['subfunction|required|unitlist', 'bool'],
        'счетмо'        => ['bool'],
        'расчет'        => ['expression|required'],
        'счетзнач'      => ['expression|required'],
        'мпдиапазон'    => ['subfunction|required|diapazon|iterator', 'boolean|required', 'subfunction|группы', 'factor'],
        'разрез'        => ['factor|required', 'factor|required','boolean|required'],
    ];

    public static $algorithms = [
        null,
        'сравнение'     => 'a1 a3 a2',
        'зависимость'   => 'a1 ^ a2',
        'межгодовой'    => '(a2 - a1)/a2 * 100 > a3',
        'мгдиапазон'    => '(a1p - a1c)/a1p * 100 > a2',
        'кратность'     => 'a1 % a2 == 0',
        'разрез'     => 'a1 a3 a2',
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
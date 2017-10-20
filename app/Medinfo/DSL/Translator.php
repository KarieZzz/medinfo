<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 11:37
 */

namespace App\Medinfo\DSL;


class Translator
{
    const NS = 'App\\Medinfo\\DSL\\';

    public static function invoke(Parser $parser, $table)
    {
        $f = $parser->function_index;
        $t = self::NS . FunctionDispatcher::$translators[$f];
        return new $t($parser, $table);

    }
}
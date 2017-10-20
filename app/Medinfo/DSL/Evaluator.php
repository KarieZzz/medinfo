<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2017
 * Time: 11:37
 */

namespace App\Medinfo\DSL;


class Evaluator
{
    const NS = 'App\\Medinfo\\DSL\\';

    public static function invoke($ptree, $properties, $document)
    {
        $f = $properties['function_id'];
        $e = self::NS . FunctionDispatcher::$evaluators[$f];
        return new $e($ptree, $properties, $document);

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 29.05.2018
 * Time: 17:38
 */

namespace App\Medinfo\DSL;


class FunctionCompiler
{

    public static function compile($script, \App\Table $table)
    {
        try {
            $lexer = new \App\Medinfo\DSL\ControlFunctionLexer($script);
            $tockenstack = $lexer->getTokenStack();
            $parser = new \App\Medinfo\DSL\ControlFunctionParser($tockenstack);
            $parser->func();
            $translator = \App\Medinfo\DSL\Translator::invoke($parser, $table);
            $translator->prepareIteration();
            $compiled_cache['ptree'] = base64_encode(serialize($translator->parser->root));
            $compiled_cache['properties'] = $translator->getProperties();

        } catch (\Exception $e) {
            $compiled_cache['compile_error'] = "Ошибка при компилляции функции: " . $e->getMessage();
        }
        return $compiled_cache;
    }
}
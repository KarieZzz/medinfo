<?php

namespace App\Http\Controllers\Tests;

use App\Medinfo\DSL\ControlFunctionLexer;
use App\Medinfo\DSL\ControlFunctionParser;
use App\Medinfo\DSL\ControlPtreeTranslator;
use App\Medinfo\DSL\Translator;
use App\Medinfo\DSL\Evaluator;
use App\Document;
use App\Table;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ControlFunctionTestController extends Controller
{
    //
    public function fold()
    {
        $table = Table::find(10);     // Ф30 Т1100

        $document = Document::find(4519); // 30 ф Салтыковский детский дом 3 кв. МСК
        $function = "кратность(диапазон(С1Г3:С224Г8, С1Г33:С224Г34, С1Г43:С224Г44, С1Г53:С224Г54), 0.25 )";

        $lexer = new ControlFunctionLexer($function);
        $tockenstack = $lexer->getTokenStack();
        //dd($tockenstack);
        //dd($lexer->normalizeInput());
        //dd($lexer);

        $parser = new ControlFunctionParser($tockenstack);
        $parser->func();
        //dd($parser);
        //dd($parser->root);
        //dd($parser->function_index);
        //dd($parser->celladressStack);
        //dd($parser->cellrangeStack);
        //dd($parser->argStack);

        $translator = Translator::invoke($parser, $table);
        //dd($translator);
        $translator->prepareIteration();
        //dd($translator->getProperties());
        //dd($translator->parser->root);
        $prop = $translator->getProperties();
        //dd($prop['iterations'][0]['С1Г3|0']);
        $evaluator = Evaluator::invoke($translator->parser->root, $translator->getProperties(), $document);
        //$evaluator = new ControlFunctionEvaluator($translator->parser->root, $translator->getProperties(), $document);
        //$evaluator = new ControlFunctionEvaluator($pTree, $props, $document);
        //$evaluator->prepareCellValues();
        //$evaluator->prepareCAstack();
        //dd($evaluator->arguments);
        //dd($evaluator->pTree);
        //dd($evaluator->caStack);
        //dd($evaluator->iterations);
        //return $evaluator->evaluate();

        //dd($evaluator->makeControl());
        //$evaluator->makeControl();
        //dd($evaluator);
        //return ($evaluator->iterations);
        //return ($evaluator->properties);
        return $evaluator->makeControl();

    }
}

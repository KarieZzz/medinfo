<?php

namespace App\Http\Controllers\Tests;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Medinfo\DSL\ControlFunctionLexer;
use App\Medinfo\DSL\ControlFunctionParser;
use App\Medinfo\DSL\ControlPtreeTranslator;
use App\Medinfo\DSL\Translator;
use App\Medinfo\DSL\Evaluator;
use App\Document;
use App\Table;

class SectionCheckTestController extends Controller
{
    // Тестирование функции контроля разрезов форм
    public function SectionCheckTest()
    {
        //$i = "сравнение(С139, С140+С141+С142, =, , графы(9,12-17))";
        $i = "разрез(12, 1201, >)";


        $lexer = new ControlFunctionLexer($i);
        $tockenstack = $lexer->getTokenStack();
        //dd($tockenstack);
        //dd($lexer->normalizeInput());
        //dd($lexer);

        $parser = new ControlFunctionParser($tockenstack);
        $parser->func();
        //dd($parser);
        //dd($parser->root);
        //dd($parser->function_index);
        //dd(json_decode(json_encode($parser->root)));
        //dd($parser->celladressStack);
        //dd($parser->cellrangeStack);
        //dd($parser->argStack);

        $table = Table::find(111);     // Ф12 Т1000

        $translator = Translator::invoke($parser, $table);
        //dd($translator);
        //$translator = new ControlPtreeTranslator($parser, $table);
        //$translator = new CompareTranslator($parser, $table);
        //$translator->setParentNodesFromRoot();
        //$translator->parseCellAdresses();
        //$translator->parseCellRanges();
        //$translator->validateVector();
        $translator->prepareIteration();
        //dd($translator);
        //dd($translator->getProperties());
        //dd($translator->parser->root);
        //dd($translator->getProperties());


        //$document = Document::find(4965); // 30 ф Все организации 3 кв. МСК
        $document = Document::find(47884); // 12 ф Все организации 3 кв. МСК

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
        $evaluator->makeControl();
        //dd($evaluator);
        //return ($evaluator->iterations);
        //return ($evaluator->properties);
        return $evaluator->makeControl();

    }
}

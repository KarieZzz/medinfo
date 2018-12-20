<?php

namespace App\Http\Controllers\Tests;

use App\Table;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CalculationFunctionTestController extends Controller
{
    //
    public function mocount()
    {
        $rule = "счетмо()"; //
        $list = "~село, поликлиники, сб";
        $table = 2; // форма 47 таблица 0100
        $document = \App\Document::find(19251);
        $trimed = preg_replace('/,+\s+/u', ' ', $list);
        $lists = array_unique(array_filter(explode(' ', $trimed)));
        $units = \App\Medinfo\DSL\FunctionCompiler::compileUnitList($lists);
        asort($units);
        $prop = '[' . implode(',', $units) . ']';

        $lexer = new \App\Medinfo\DSL\ControlFunctionLexer($rule);
        $tockenstack = $lexer->getTokenStack();
        $parser = new \App\Medinfo\DSL\ControlFunctionParser($tockenstack);
        $parser->func();
        $translator = \App\Medinfo\DSL\Translator::invoke($parser, Table::find($table));
        //$translator->setUnits($units);
        $translator->prepareIteration();
        //dd($translator);
        //dd($translator->getProperties());
        $props = $translator->getProperties();
        $props['units'] = $units;
        //dd($props);
        //$evaluator = \App\Medinfo\DSL\Evaluator::invoke($translator->parser->root, $translator->getProperties(), $document);
        $evaluator = \App\Medinfo\DSL\Evaluator::invoke($translator->parser->root, $props, $document);
        $evaluator->makeConsolidation();
        //dd($evaluator->calculationLog);
        echo $evaluator->evaluate();
    }

    public function calculation()
    {
        $rule = "расчет(Ф30Т2100С1Г3-Ф30Т2100С86Г3-Ф30Т2100С87Г3-Ф30Т2100С89Г3-Ф30Т2100С90Г3)"; //
        $list = "~село, поликлиники, сб";
        $table = 2; // форма 47 таблица 0100
        $document = \App\Document::find(19251);
        $trimed = preg_replace('/,+\s+/u', ' ', $list);
        $lists = array_unique(array_filter(explode(' ', $trimed)));
        $units = \App\Medinfo\DSL\FunctionCompiler::compileUnitList($lists);
        asort($units);
        $prop = '[' . implode(',', $units) . ']';

        $lexer = new \App\Medinfo\DSL\ControlFunctionLexer($rule);
        $tockenstack = $lexer->getTokenStack();
        $parser = new \App\Medinfo\DSL\ControlFunctionParser($tockenstack);
        $parser->func();
        $translator = \App\Medinfo\DSL\Translator::invoke($parser, Table::find($table));
        //$translator->setUnits($units);
        $translator->prepareIteration();
        //dd($translator);
        //dd($translator->getProperties());
        $props = $translator->getProperties();
        $props['units'] = $units;
        //dd($props);
        //$evaluator = \App\Medinfo\DSL\Evaluator::invoke($translator->parser->root, $translator->getProperties(), $document);
        $evaluator = \App\Medinfo\DSL\Evaluator::invoke($translator->parser->root, $props, $document);
        $evaluator->makeConsolidation();
        //dd($evaluator->calculationLog);
        //echo $evaluator->evaluate();
        foreach ($evaluator->calculationLog as &$el) {
            $unit = \App\Unit::find($el['unit_id']);
            $el['unit_name'] = $unit->unit_name;
            $el['unit_code'] = $unit->unit_code;
        }

        $log_initial = collect($evaluator->calculationLog);
        //$log_sorted = $log_initial->sortBy('unit_code');
        $log_c_sorted = $log_initial->sortBy('unit_code');
        //dd($log);
        $log_sorted = [];
        foreach ($log_c_sorted as $el ) {
            $log_sorted[] = $el;
        }
        //dd($log_sorted);

        //echo(json_encode($log->toArray()));
        $log = json_encode($log_sorted);
        echo $log;

    }

}

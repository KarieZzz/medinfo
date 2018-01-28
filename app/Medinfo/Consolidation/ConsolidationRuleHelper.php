<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 12.12.2017
 * Time: 19:13
 */

namespace App\Medinfo\Consolidation;


class ConsolidationRuleHelper
{
    public static function getTableRules(\App\Table $table) {
        $rows = $table->rows->sortBy('row_index')->pluck('id')->toArray();
        //dd($rows);
        //dd(in_array(5445, $rows));
        $cols = $table->columns->sortBy('column_index')->pluck('id')->toArray();
        $rules = \App\ConsolidationRule::WhereIn('row_id', $rows)->WhereIn('col_id', $cols)->get();
        return $rules;
    }

    public static function evaluateRule(\App\ConsolidationRule $rule, \App\Document $document, \App\Table $table)
    {
        $lexer = new \App\Medinfo\DSL\ControlFunctionLexer($rule->script);
        $tockenstack = $lexer->getTokenStack();
        $parser = new \App\Medinfo\DSL\ControlFunctionParser($tockenstack);
        $parser->func();
        $translator = \App\Medinfo\DSL\Translator::invoke($parser, $table);
        $translator->prepareIteration();
        $evaluator = \App\Medinfo\DSL\Evaluator::invoke($translator->parser->root, $translator->getProperties(), $document);
        $evaluator->makeConsolidation();
        return $evaluator->evaluate();
    }

}
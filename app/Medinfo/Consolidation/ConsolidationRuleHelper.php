<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 12.12.2017
 * Time: 19:13
 */

namespace App\Medinfo\Consolidation;


use Carbon\Carbon;

class ConsolidationRuleHelper
{
    public static function getTableRules(\App\Table $table) {
        $rows = $table->rows->sortBy('row_index')->pluck('id')->toArray();
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
        self::logConsolidation($evaluator->calculationLog, $document->id, $rule->row_id, $rule->col_id);
        return $evaluator->evaluate();
    }

    public static function logConsolidation(array $calculationLog, int $doc_id, int $row_id, int $col_id)
    {
        foreach ($calculationLog as &$el) {
            $unit = \App\Unit::find($el['unit_id']);
            $el['unit_name'] = $unit->unit_name;
            $el['unit_code'] = $unit->unit_code;
        }
        $log = \App\Consolidate::firstOrNew( ['doc_id' => $doc_id, 'row_id' => $row_id, 'column_id' => $col_id ]);
        $log->protocol = json_encode($calculationLog);
        $log->consolidated_at = Carbon::create();
        $log->save();
        //dd($log);
        return $log;
    }

}
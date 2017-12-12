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
        $rows = $table->rows->sortBy('row_index');
        $cols = $table->columns->sortBy('column_index');
        $data = array();
        $i=0;
        foreach ($rows as $row) {
            $r = [];
            foreach($cols as $col) {
                if ($col->content_type == \App\Column::DATA) {
                    if(!is_null($rule = \App\ConsolidationRule::OfRC($row->id, $col->id)->first())) {
                        $r['row'] = $row->id;
                        $r['column'] = $col->id;
                        $r['script'] = $rule->script;
                    }
                }
            }
            if (count($r) > 0) {
                $data[$i] = $r;
                $i++;
            }
        }
        return $data;
    }

    public static function evaluateRule(Array $rule, \App\Document $document, \App\Table $table)
    {
        $lexer = new \App\Medinfo\DSL\ControlFunctionLexer($rule['script']);
        $tockenstack = $lexer->getTokenStack();
        $parser = new \App\Medinfo\DSL\ControlFunctionParser($tockenstack);
        $parser->func();
        $translator = \App\Medinfo\DSL\Translator::invoke($parser, $table);
        $translator->prepareIteration();
        $evaluator = \App\Medinfo\DSL\Evaluator::invoke($translator->parser->root, $translator->getProperties(), $document);
        return $evaluator->evaluate();
    }

}
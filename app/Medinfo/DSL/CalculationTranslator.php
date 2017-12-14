<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 13.12.2017
 * Time: 17:49
 */

namespace App\Medinfo\DSL;


class CalculationTranslator extends ControlPtreeTranslator
{
    public function parseGroupScopes()
    {
        $includes = [];
        $excludes = [];
        if (count($this->parser->includeGroupStack) > 0 || count($this->parser->excludeGroupStack) > 0) {
            $this->scopeOfUnits = true;
            foreach ($this->parser->includeGroupStack as $list_slug) {
                $list = \App\UnitList::Slug($list_slug)->first();
                if (is_null($list)) {
                    throw new \Exception("Группа $list_slug не существует");
                }
                $includes = array_merge($includes, $list->members->pluck('ou_id')->toArray());
            }
            //dd($includes);
            //dd($excludes);
            $this->units = array_diff($includes, $excludes);
        }
    }
}
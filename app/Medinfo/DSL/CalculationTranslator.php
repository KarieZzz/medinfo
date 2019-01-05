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

/*    public function parseGroupScopes()
    {
        $includes = [];
        $includes_w_sub = [];
        $excludes = [];
        $excludes_w_sub = [];

        if (count($this->parser->includeGroupStack) > 0 || count($this->parser->excludeGroupStack) > 0) {
            $this->scopeOfUnits = true;
            foreach ($this->parser->includeGroupStack as $listin_slug) {
                $list_in = \App\UnitList::Slug($listin_slug)->first();
                if (is_null($list_in)) {
                    throw new \Exception("Группа $listin_slug не существует");
                }
                $includes = array_merge($includes, $list_in->members->pluck('ou_id')->toArray());
            }
            foreach ($this->parser->excludeGroupStack as $listex_slug) {
                $list_ex = \App\UnitList::Slug($listex_slug)->first();
                if (is_null($list_ex)) {
                    throw new \Exception("Группа $listex_slug не существует");
                }
                $excludes = array_merge($includes, $list_ex->members->pluck('ou_id')->toArray());
            }
            foreach ($includes as $include) {
                $list_in_w_sub = \App\Unit::getDescendants($include);
                $includes_w_sub = array_merge($includes_w_sub, $list_in_w_sub);
            }
            foreach ($excludes as $exclude) {
                $list_ex_w_sub = \App\Unit::getDescendants($exclude);
                $excludes_w_sub = array_merge($excludes_w_sub, $list_ex_w_sub);
            }
            //dd($includes);
            //dd($excludes);
            //dd($includes_w_sub);
            //$this->units = array_diff($includes, $excludes);
            $this->units = array_diff($includes_w_sub, $excludes_w_sub);
        }
    }*/
}
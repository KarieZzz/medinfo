<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 29.05.2018
 * Time: 17:38
 */

namespace App\Medinfo\DSL;


use Illuminate\Support\Collection;

class FunctionCompiler
{

    public static function compileRule($script, \App\Table $table)
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

    public static function compileUnitList(array $lists)
    {
        //$units = Collection::make();
        $addlists = [];
        $subtractlists = [];
        $limitationlists = [];

        $units = [];
        $addunits = [];
        $subtractunits = [];
        $limitationunits = [];
        $reserved = config('medinfo.reserved_unitlist_slugs');
        //dd($lists);
        foreach ($lists as $list) {
            $prefix = $list[0];
            switch ($prefix) {
                case '!' :
                    $list = substr($list, 1);
                    $subtractlists[] = $list;
                    break;
                case '~' :
                    $list = substr($list, 1);
                    $u = \App\UnitList::Slug($list)->first();
                    if (is_null($u)) {
                        throw new \Exception("Список '$list' не существует");
                    }
                    $lm = $u->members->pluck('ou_id');
                    $limitationlist = array_merge($limitationunits, $lm->toArray());
                    break;
                default:
                    $u = \App\UnitList::Slug($list)->first();
                    if (is_null($u)) {
                        throw new \Exception("Список '$list' не существует");
                    }
                    $add = $u->members->pluck('ou_id');
                    $addunits = array_merge($addunits, $add->toArray());
            }
        }

/*        foreach ($lists as $list) {
            $prefix = $list[0];
            switch ($prefix) {
                case '!' :
                    $list = substr($list, 1);
                    $u = \App\UnitList::Slug($list)->first();
                    if (is_null($u)) {
                        throw new \Exception("Список '$list' не существует");
                    }
                    $sub = $u->members->pluck('ou_id');
                    //dd($sub->toArray());
                    $subtractunits = array_merge($subtractunits, $sub->toArray());
                    break;
                case '~' :
                    $list = substr($list, 1);
                    $u = \App\UnitList::Slug($list)->first();
                    if (is_null($u)) {
                        throw new \Exception("Список '$list' не существует");
                    }
                    $lm = $u->members->pluck('ou_id');
                    $limitationlist = array_merge($limitationunits, $lm->toArray());
                    break;
                default:
                    $u = \App\UnitList::Slug($list)->first();
                    if (is_null($u)) {
                        throw new \Exception("Список '$list' не существует");
                    }
                    $add = $u->members->pluck('ou_id');
                    $addunits = array_merge($addunits, $add->toArray());
            }
        }*/

        $addunits = array_unique($addunits);
        $subtractunits = array_unique($subtractunits);
        $limitationlist = array_unique($limitationunits);

        $units = array_diff($addunits, $subtractunits);
        if (count($limitationlist) > 0) {
            //dd($limitationlist);
            $units = array_intersect($units, $limitationunits);
        }
        //units = array_values($units);
        //dd($units);
        return \App\Unit::whereIn('id', $units)->get(['id', 'unit_code', 'unit_name'])->sortBy('unit_code');
        //return $units;
    }
}
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
        $addlists = [];
        $subtractlists = [];
        $limitationlists = [];
        $units = [];
        $addunits = [];
        $subtractunits = [];
        $limitationunits = [];
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
                    $limitationlists[] = $list;
                    break;
                default:
                    $addlists[] = $list;
            }
        }
        $addunits = array_merge($addunits, self::getUnitsFromLists($addlists));
        $subtractunits = array_merge($subtractunits, self::getUnitsFromLists($subtractlists));
        $limitationunits = array_merge($limitationunits, self::getUnitsFromLists($limitationlists));
        $addunits = array_unique($addunits);
        $subtractunits = array_unique($subtractunits);
        $limitationunits = array_unique($limitationunits);
        $units = array_diff($addunits, $subtractunits);
        if (count($limitationunits) > 0) {
            $units = array_intersect($units, $limitationunits);
        }

        //return \App\Unit::whereIn('id', $units)->get(['id', 'unit_code', 'unit_name'])->sortBy('unit_code');
        return $units;
    }

    public static function getUnitsFromLists(array $lists)
    {
        $units = [];
        foreach ($lists as $list) {
            if (in_array($list, config('medinfo.reserved_unitlist_slugs'))) {
                $units = self::getUnitsFromReserved($list);
            } else {
                $u = \App\UnitList::Slug($list)->first();
                if (is_null($u)) {
                    throw new \Exception("Список '$list' не существует");
                }
                $units = array_merge($units, $u->members->pluck('ou_id')->toArray());
            }
        }
        return $units;
    }

    public static function getUnitsFromReserved(string $staticlist)
    {
        $units = [];
        switch ($staticlist) {
            case 'оп' :
            case 'обособподр' :
                $units = \App\Unit::SubLegal()->get()->pluck('id')->toArray();
                break;
            case 'юл' :
            case 'юрлица' :
                $units = \App\Unit::Legal()->get()->pluck('id')->toArray();
                break;
            case 'село' :
                $units = \App\Unit::Country()->get()->pluck('id')->toArray();
                break;
            default :
                throw new \Exception("Статический список/группа '$staticlist' не существует");
        }
        return $units;
    }

}
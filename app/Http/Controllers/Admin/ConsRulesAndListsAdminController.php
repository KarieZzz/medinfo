<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Flysystem\Exception;

class ConsRulesAndListsAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $forms = \App\Form::orderBy('form_code')->get(['id', 'form_code', 'form_name']);
        return view('jqxadmin.set_consrules_and_lists', compact('forms'));
    }

    public function getRule(\App\Row $row, \App\Column $column)
    {
        $scripts = ['rule' => '', 'list' => ''];
        $rule_using = \App\ConsUseRule::OfRC($row->id, $column->id)->first();
        $scripts['rule'] = is_null($rule_using) ? '' : $rule_using->rulescript->script;
        $list_using = \App\ConsUseList::OfRC($row->id, $column->id)->first();
        $scripts['list'] = is_null($list_using) ? '' : $list_using->listscript->script;

        return $scripts;
    }

    public function applyRule(Request $request)
    {
        $this->validate($request, $this->validateRuleRequest());
        $coordinates = explode(',', $request->cells);
        $hashed  =  sprintf("%u", crc32(preg_replace('/\s+/u', '', $request->rule)));
        $table = \App\Table::find(2);
        $compiled = \App\Medinfo\DSL\FunctionCompiler::compileRule($request->rule, $table);
        //dd($compiled['properties']);
        //dd($request->rule);
        //dd($hashed);
        $rule = \App\ConsolidationCalcrule::firstOrNew(['hash' => $hashed]);
        $rule->script = $request->rule;
        $rule->ptree = $compiled['ptree'];
        $rule->properties = json_encode($compiled['properties']);
        $rule->save();
        $i = 0;
        foreach ($coordinates as $coordinate) {
            list($row, $column) = explode('_', $coordinate);
            $apply_rule = \App\ConsUseRule::firstOrNew(['row_id' => $row, 'col_id' => $column]);
            $apply_rule->script = $rule->id;
            $apply_rule->save();
            $i++;
        }
        return ['affected_cells' => $i ];
    }

    public function applyList(Request $request)
    {
        $error = null;
        $this->validate($request, $this->validateListRequest());
        $coordinates = explode(',', $request->cells);
        $trimed = preg_replace('/,+\s+/u', ' ', $request->list);
        $lists = array_unique(array_filter(explode(' ', $trimed)));
        array_multisort($lists, SORT_NATURAL);
        $glued = implode(', ', $lists);
        //$hashed  =  sprintf("%u", crc32(implode('', $lists)));
        //dd($hashed);
        //if ($list->script !== $glued) {
        //dd($lists);
            try {
                $units = \App\Medinfo\DSL\FunctionCompiler::compileUnitList($lists);
                asort($units);
                $prop = '[' . implode(',', $units) . ']';
                $hashed  =  crc32($prop);
                $list = \App\ConsolidationList::firstOrNew(['hash' => $hashed]);
                $list->script = $glued;
                //$list->properties = $units->toJson();
                $list->properties = $prop;
                $list->hash = $hashed;
                //$list->properties = json_encode([ 'units' => [1,5,2,3] ]);
                //dd($list->properties);
                $list->save();
                $i = 0;
                foreach ($coordinates as $coordinate) {
                    list($row, $column) = explode('_', $coordinate);
                    $apply_list = \App\ConsUseList::firstOrNew(['row_id' => $row, 'col_id' => $column]);
                    $apply_list->list = $list->id;
                    $apply_list->save();
                    $i++;
                }
                return ['affected_cells' => $i ];
            } catch (\Exception $e) {
                return ['affected_cells' => 0, 'error' => $e->getMessage() ];
            }
    }

    public function clearRule(Request $request)
    {
        $this->validate($request, [ 'cells' => 'required', ] );
        $coordinates = explode(',', $request->cells);
        $i = 0;
        foreach ($coordinates as $coordinate) {
            list($row, $column) = explode('_', $coordinate);
            $ruleusing = \App\ConsUseRule::OfRC($row, $column)->first();
            if (!is_null($ruleusing)) {
                $ruleusing->delete();
                $i++;
            }
        }
        return ['affected_cells' => $i ];
    }

    public function clearList(Request $request)
    {
        $this->validate($request, [ 'cells' => 'required', ] );
        $coordinates = explode(',', $request->cells);
        $i = 0;
        foreach ($coordinates as $coordinate) {
            list($row, $column) = explode('_', $coordinate);
            $listusing = \App\ConsUseList::OfRC($row, $column)->first();
            if (!is_null($listusing)) {
                $listusing->delete();
                $i++;
            }
        }
        return ['affected_cells' => $i ];
    }

    protected function validateListRequest()
    {
        return [
            'list' => 'required|min:1|max:512',
            'comment' => 'max:128',
            'cells' => 'required',
        ];
    }

    protected function validateRuleRequest()
    {
        return [
            'rule' => 'required|min:1|max:512',
            'comment' => 'max:128',
            'cells' => 'required',
        ];
    }

}

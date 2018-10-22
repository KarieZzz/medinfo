<?php

namespace App\Http\Controllers\Analytics;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Document;
use App\CFunction;
use App\Medinfo\DSL\Evaluator;
use App\Medinfo\Control\ControlHelper;
use \Session;
//use App\Table;
//use App\Unit;

class SelectedCFunctionCheckController extends Controller
{
    //
    public $ptree;
    public $props;

    public function __construct()
    {
        //$this->middleware('admins');
    }

    public function index(CFunction $cf)
    {
        //dd($cf);
        $upper_levels = \App\UnitsView::whereIn('type', ['1','2','100'])->get();
        $table = $cf->table;
        $form = $table->form;
        $monitorings = \App\Monitoring::all();
        $periods = \App\Period::all();
        Session::put('checked', 0);
        Session::put('count_of_docs', 0);
        Session::put('result', '');
        Session::save();
        return view('jqxadmin.selectedcontrolconditions', compact('cf', 'upper_levels', 'table', 'form', 'monitorings', 'periods'));
    }

    public function performControl(Request $request)
    {
        $this->validate($request, [
                'script' => 'required',
                'monitoring' => 'required|integer',
                'period' => 'required|integer',
                'ou' => 'required|integer',
                'type' => 'required|integer',
                'form' => 'required|integer',
                'table' => 'required|integer',
            ]
        );
        if ($request->type  === '100') {
            $units = \App\UnitListMember::List($request->ou)->pluck('ou_id');
        } else {
            $units = \App\Unit::getDescendants($request->ou);
        }
        //$table = Table::find($request->table);
        $this->prepareScript($request->script, $request->table);
        $documents = Document::Primary()
            ->OfMonitoring($request->monitoring)
            ->OfForm($request->form)
            ->OfPeriod($request->period)
            ->whereIn('ou_id', $units)->get();
        $protocol = [];
        $evaluator = Evaluator::invoke($this->ptree, $this->props);
        $doc_count = $documents->count();
        $empy_docs = 0;
        Session::put('count_of_docs', $doc_count);
        Session::put('in_progress', true);
        for ($i = 0; $i < $doc_count; $i++) {
            $protocol[$i]['doc_id'] = $documents[$i]->id;
            $protocol[$i]['unit_code'] = $documents[$i]->unit->unit_code;
            $protocol[$i]['unit_name'] = $documents[$i]->unit->unit_name;
            $protocol[$i]['valid'] = true;
            ControlHelper::formContainsData($documents[$i]->id) ? $protocol[$i]['no_data'] = false : $protocol[$i]['no_data'] = true;
            if ($protocol[$i]['no_data'] === false) {
                $evaluator->setDocument($documents[$i]);
                $protocol[$i]['iterations'] = $evaluator->makeControl();
                $protocol[$i]['valid'] = $evaluator->valid;
            } else {
                $empy_docs++;
            }
            Session::put('checked', $i);
            //Session::put('current_unit', $protocol[$i]['unit_name']);
            Session::save();
        }
        Session::put('in_progress', false);
        Session::put('result', ['compile' => true, 'doc_count' => $doc_count, 'empty_docs' => $empy_docs, 'protocol' => $protocol ]);
        Session::save();
        return ['ended' => true, 'protocol' => $protocol];
        //return ['compile' => true, 'doc_count' => $doc_count, 'empty_docs' => $empy_docs, 'protocol' => $protocol ];
    }

    public function prepareScript($script, $table_id)
    {
        $lexer = new \App\Medinfo\DSL\ControlFunctionLexer($script);
        $table = \App\Table::find($table_id);
        $tockenstack = $lexer->getTokenStack();
        $parser = new \App\Medinfo\DSL\ControlFunctionParser($tockenstack);
        $parser->func();
        $translator = \App\Medinfo\DSL\Translator::invoke($parser, $table);
        $translator->prepareIteration();
        $this->ptree = $translator->parser->root;
        $this->props = $translator->getProperties();
    }

    public function getProgess() {
        $session_id = Session::getId();
        $managed = Session::get('checked');
        //$current_unit = trim(Session::get('current_unit'));
        $count_of_docs = Session::get('count_of_docs');
        $result = null;
        if (Session::get('in_progress')) {
            $progress = round($managed/$count_of_docs*100, 2);
            $ended = false;
        } else {
            $progress = 100;
            $ended = true;
            $result = Session::get('result');
        }
        return compact('session_id', 'managed', 'count_of_docs', 'progress' , 'ended', 'result') ;
    }

}

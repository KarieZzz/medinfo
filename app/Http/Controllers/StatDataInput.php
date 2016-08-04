<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
//use App\Worker;
use App\WorkerScope;
use App\Medinfo\PeriodMM;
use App\Medinfo\UnitTree;
use App\Medinfo\DocumentTree;
use App\Period;
use App\Form;
use App\DicDocumentState;

class StatDataInput extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function index()
    {
        $worker = Auth::guard('datainput')->user();
        $worker_scope = WorkerScope::where('worker_id', $worker->id)->first()->ou_id;
        $permission = $worker->permission;
        //$period = new PeriodMM(config('app.default_period'));
        //$period_id = $period->getTableName();
        $disabled_states = config('app.disabled_states.' . $worker->role);
        if (!is_null($worker_scope)) {
            $mo_tree = UnitTree::getSimpleTree();
        }
        if ($permission & config('app.permission.permission_audit_document')) {
            $audit_permission = true;
        }
        else {
            $audit_permission = false;
        }
        $forms = Form::orderBy('form_index', 'desc')->get(['id', 'form_code']);
        $form_ids = $forms->pluck('id');
        $states = DicDocumentState::all(['code', 'name']);
        $state_ids = $states->pluck('code');
        $periods = Period::orderBy('begin_date', 'desc')->get(['id', 'name']);
        $period_ids = $periods[0]->id;
        return view('jqxdatainput.documentdashboard', compact('mo_tree', 'worker', 'worker_scope', 'periods', 'period_ids',
            'disabled_states', 'audit_permission', 'forms', 'form_ids', 'states', 'state_ids'));
    }

    public function fetchdocuments(Request $request)
    {
        $top_node = $request->ou;
        $dtypes[] = 1;
        $states = explode(",", $request->states);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('top_node', 'dtypes', 'states', 'forms', 'periods');
        $d = new DocumentTree($scopes);
        $data = $d->get_documents();
        return $data;
    }

    public function fetchaggregates(Request $request)
    {
        $top_node = $request->ou;
        $dtypes[] = 2;
        $states = array();
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('top_node', 'dtypes', 'states', 'forms', 'periods');
        $d = new DocumentTree($scopes);
        $data = $d->get_aggregates();
        return $data;
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Worker;
use App\WorkerScope;
use App\Medinfo\PeriodMM;
use App\Medinfo\UnitTree;
use App\Medinfo\DocumentTree;

class StatDataInput extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function index()
    {
        $worker_id = Auth::guard('datainput')->user()->id;
        $worker = Worker::find($worker_id);
        $worker_scope = WorkerScope::where('worker_id', $worker_id)->first()->ou_id;
        $permission = $worker->permission;
        $period = new PeriodMM(config('app.default_period'));
        $period_id = $period->getTableName();
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
        return view('jqxdatainput.documentdashboard', compact('mo_tree', 'worker', 'worker_scope', 'period_id', 'disabled_states', 'audit_permission'));
    }

    public function fetchdocuments(Request $request)
    {
        $top_node = $request->ou;
        $states = explode(",", $request->states);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('top_node', 'states', 'forms', 'periods');
        //$scopes_ = array('top_node' => $top_node, 'states' => $states, 'forms' => $forms, 'periods' => $periods );
        $d = new DocumentTree($scopes);
        $data = $d->get_documents();
        return $data;
    }

    public function fetchaggregates(Request $request)
    {
        $top_node = $request->ou;
        $states = array();
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('top_node', 'states', 'forms', 'periods');
        $d = new DocumentTree($scopes);
        $data = $d->get_aggregates();
        return $data;
    }

}

<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\WorkerScope;
use App\UnitGroup;
use App\Medinfo\UnitTree;
use App\Medinfo\DocumentTree;
use App\Period;
use App\Form;
use App\DicDocumentState;

class DocumentDashboardController extends Controller
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
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        $form_ids = $forms->pluck('id');
        $states = DicDocumentState::all(['code', 'name']);
        $state_ids = $states->pluck('code');
        $periods = Period::orderBy('begin_date', 'desc')->get(['id', 'name']);
        // Периоды отображаемые по умолчанию (поставил последний и предпоследний по датам убывания)
        $period_ids = $periods[0]->id . ',' . $periods[1]->id;
        return view('jqxdatainput.documentdashboard', compact('mo_tree', 'worker', 'worker_scope', 'periods', 'period_ids',
            'disabled_states', 'audit_permission', 'forms', 'form_ids', 'states', 'state_ids'));
    }

    public function fetch_mo_hierarchy($parent = 0)
    {
        //return UnitTree::getSimpleTree($parent);
        return UnitTree::getMoTree($parent);
    }

    public function fetch_unitgroups()
    {
        //return UnitTree::getSimpleTree($parent);
        return UnitGroup::all();
    }

    public function fetchdocuments(Request $request)
    {
        $top_node = $request->ou;
        $filter_mode = $request->filter_mode;
        $dtypes[] = 1;
        $states = explode(",", $request->states);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('filter_mode', 'top_node', 'dtypes', 'states', 'forms', 'periods');
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

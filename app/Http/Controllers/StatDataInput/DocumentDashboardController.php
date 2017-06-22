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
use App\WorkerSetting;
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
          $disabled_states = config('medinfo.disabled_states.' . $worker->role);
        if (!is_null($worker_scope)) {
            $mo_tree = UnitTree::getSimpleTree();
        }
        if ($permission & config('medinfo.permission.permission_audit_document')) {
            $audit_permission = true;
        }
        else {
            $audit_permission = false;
        }
        //$forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        $forms = WorkerSetting::where('worker_id', $worker->id)->where('name','monitorings')->first(['value']);
        //dd($forms);
        //$form_ids = $forms->pluck('value');
        $states = DicDocumentState::all(['code', 'name']);
        $state_ids = WorkerSetting::where('worker_id', $worker->id)->where('name','states')->first(['value']);
        $periods = Period::orderBy('begin_date', 'desc')->get(['id', 'name']);
        // Периоды отображаемые по умолчанию (поставил последний и предпоследний по датам убывания)
        //$period_ids = $periods[0]->id . ',' . $periods[1]->id;
        $period_ids = WorkerSetting::where('worker_id', $worker->id)->where('name','periods')->first(['value']);
        return view('jqxdatainput.documentdashboard', compact('mo_tree', 'worker', 'worker_scope', 'periods', 'period_ids',
            'disabled_states', 'audit_permission', 'forms', 'form_ids', 'states', 'state_ids'));
    }

    public function fetch_monitorings()
    {
        //return \App\MonitoringView::take(3)->get()->toJson();
        return \App\MonitoringView::orderBy('name')->get();
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
        $worker = Auth::guard('datainput')->user();
        $worker_scope = WorkerScope::where('worker_id', $worker->id)->first()->ou_id;
        $top_node = $request->ou;
        $filter_mode = $request->filter_mode;
        $dtypes[] = 1;
        $states = explode(",", $request->states);
        $monitorings = explode(",", $request->monitorings);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('worker_scope', 'filter_mode', 'top_node', 'dtypes', 'states', 'monitorings', 'forms', 'periods');
        $this->saveLastState($request, $worker);
        $d = new DocumentTree($scopes);
        $data = $d->get_documents();
        return $data;
    }

    public function fetchaggregates(Request $request)
    {
        $worker = Auth::guard('datainput')->user();
        $worker_scope = WorkerScope::where('worker_id', $worker->id)->first()->ou_id;
        $top_node = $request->ou;
        $filter_mode = $request->filter_mode;
        $dtypes[] = 2;
        $states = array();
        $monitorings = explode(",", $request->monitorings);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('worker_scope', 'filter_mode', 'top_node', 'dtypes', 'states', 'monitorings', 'forms', 'periods');
        $d = new DocumentTree($scopes);
        $data = $d->get_aggregates();
        return $data;
    }

    // Сохранение настроек отображения рабочего стола с документами
    public function saveLastState(Request $request, $worker)
    {
        $last_monitorings = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'monitorings']);
        $last_monitorings->value = $request->mf;
        $last_monitorings->save();
        $last_forms = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'forms']);
        $last_forms->value = $request->forms;
        $last_forms->save();
        $last_periods = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'periods']);
        $last_periods->value = $request->periods;
        $last_periods->save();
        $last_states = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'states']);
        $last_states->value = $request->states;
        $last_states->save();
    }

    protected function getLastState(GenericUser $worker, Document $document, Form $form, $default_album)
    {
        $laststate = array();
        $current_table = Table::OfForm($form->id)->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album->id)->orderBy('table_code');
        })->first();
        //$current_table = $form->tables->where('deleted', 0)->sortBy('table_code')->first();
        $laststate['currenttable'] = $current_table;
        return $laststate;
    }
}

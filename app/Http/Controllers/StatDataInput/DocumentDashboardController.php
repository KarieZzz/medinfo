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
        $worker_scope_get = WorkerScope::where('worker_id', $worker->id)->first();
        is_null($worker_scope_get) ? dd('Не указан перечень учрежденй, к которым имеет доступ пользователь') : $worker_scope = $worker_scope_get->ou_id;
        $last_scope_get = WorkerSetting::where('worker_id', $worker->id)->where('name','ou')->first();
        is_null($last_scope_get) ? $last_scope = $worker_scope : $last_scope = $last_scope_get->value;
        $filter_mode = WorkerSetting::where('worker_id', $worker->id)->where('name','filter_mode')->first(['value']);
        $permission = $worker->permission;
        $disabled_states = config('medinfo.disabled_states.' . $worker->role);
/*        if (!is_null($worker_scope)) {
            $mo_tree = UnitTree::getSimpleTree();
        }*/
        if ($permission & config('medinfo.permission.permission_audit_document')) {
            $audit_permission = true;
        }
        else {
            $audit_permission = false;
        }
        //$forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        $mon_ids = WorkerSetting::where('worker_id', $worker->id)->where('name','monitorings')->first(['value']);
        $form_ids = WorkerSetting::where('worker_id', $worker->id)->where('name','forms')->first(['value']);
        $mf = WorkerSetting::where('worker_id', $worker->id)->where('name','mf')->first(['value']);
        //dd($form_ids);
        //$form_ids = $forms->pluck('value');
        $states = DicDocumentState::orderBy('code')->get();
        $state_ids = WorkerSetting::where('worker_id', $worker->id)->where('name','states')->first(['value']);
        $periods = Period::orderBy('begin_date', 'desc')->get(['id', 'name']);
        // Периоды отображаемые по умолчанию (поставил последний и предпоследний по датам убывания)
        //$period_ids = $periods[0]->id . ',' . $periods[1]->id;
        $period_ids = WorkerSetting::where('worker_id', $worker->id)->where('name','periods')->first(['value']);
        $filleddocs = WorkerSetting::where('worker_id', $worker->id)->where('name','filleddocs')->first(['value']);
        return view('jqxdatainput.documentdashboard', compact( 'worker', 'worker_scope', 'last_scope', 'filter_mode', 'periods', 'period_ids',
            'disabled_states', 'audit_permission', 'mf', 'mon_ids', 'form_ids', 'states', 'state_ids', 'filleddocs'));
    }

    public function fetch_monitorings()
    {
        //return \App\MonitoringView::take(3)->get()->toJson();
        return \App\MonitoringView::orderBy('name')->get();
    }

    public function fetch_mo_hierarchy($parent = 0)
    {
        //return UnitTree::getSimpleTree($parent);
        //return UnitTree::getMoTree((int)$parent);
        $worker = Auth::guard('datainput')->user();
        return UnitTree::getMoTreeByWorker($worker->id);
    }

    public function fetch_unitgroups()
    {
        //return UnitTree::getSimpleTree($parent);
        //return UnitGroup::all();
        return \App\UnitList::OnFrontend()->get();
    }

    public function fetchdocuments(Request $request)
    {
        $worker = Auth::guard('datainput')->user();
        $worker_scope = WorkerScope::where('worker_id', $worker->id)->first()->ou_id;
        $top_node = $request->ou === 0 ? $worker_scope : $request->ou;
        $filter_mode = $request->filter_mode;
        $dtypes[] = 1;
        $states = explode(",", $request->states);
        $monitorings = explode(",", $request->monitorings);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        if ($request->filled === '-1') {
            $filled = null;
        } elseif ($request->filled === '1') {
            $filled = true;
        } elseif ($request->filled === '0') {
            $filled = false;
        }
        $scopes = compact('worker_scope', 'filter_mode', 'top_node', 'dtypes', 'states', 'monitorings', 'forms', 'periods', 'filled');
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

    public function fetchconsolidates(Request $request)
    {
        $worker = Auth::guard('datainput')->user();
        $worker_scope = WorkerScope::where('worker_id', $worker->id)->first()->ou_id;
        $top_node = $request->ou;
        $filter_mode = $request->filter_mode;
        $dtypes[] = 3;
        $states = array();
        $monitorings = explode(",", $request->monitorings);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('worker_scope', 'filter_mode', 'top_node', 'dtypes', 'states', 'monitorings', 'forms', 'periods');
        $d = new DocumentTree($scopes);
        $data = $d->get_consolidates();
        return $data;
    }

    public function fetchRecentDocuments()
    {
        $worker = Auth::guard('datainput')->user();
        return \App\RecentDocument::OfWorker($worker->id)
            ->orderBy('occured_at','desc')
            ->with('document.unitsview', 'document.monitoring', 'document.period' , 'document.form', 'document.state')->take(20)->get();
    }

    // Сохранение настроек отображения рабочего стола с документами
    public function saveLastState(Request $request, $worker)
    {
        $last_filter_mode = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'filter_mode']);
        $last_filter_mode->value = $request->filter_mode;
        $last_filter_mode->save();
        $last_ou = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'ou']);
        $last_ou->value = $request->ou;
        $last_ou->save();
        $last_mf = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'mf']);
        $last_mf->value = $request->mf;
        $last_mf->save();
        $last_monitorings = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'monitorings']);
        $last_monitorings->value = $request->monitorings;
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
        $last_states = WorkerSetting::firstOrCreate(['worker_id' => $worker->id, 'name' => 'filleddocs']);
        $last_states->value = $request->filled;
        $last_states->save();
    }

    protected function getLastState($worker)
    {
        $laststate = array();
        //$laststate['currenttable'] = $current_table;
        return $laststate;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Monitoring;

class MonitoringController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $periodicities =  \App\DicPeriodicity::all(['code', 'name']);
        $albums =  \App\Album::all(['id', 'album_name']);
        return view('jqxadmin.monitorings', compact('periodicities', 'albums'));
    }

    public function create()
    {

    }

    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules() );
        try {
            $newmon = new Monitoring;
            $newmon->name = $request->name;
            $newmon->periodicity = $request->periodicity;
            $newmon->accumulation = ($request->accumulation == 0 ? null : true);
            $newmon->album_id = $request->album;
            $newmon->save();
            return ['message' => 'Новая запись создана. Id:' . $newmon->id, 'id' => $newmon->id, ];
        } catch (\Illuminate\Database\QueryException $e) {
            return($this->error_message($e->errorInfo[0]));
        }
    }

    public function show()
    {

    }

    public function edit()
    {

    }

    public function update($monitoring, Request $request)
    {
        $mon = Monitoring::find($monitoring);
        $rules = $this->validateRules();
        $rules['name'] = 'required|max:256|';
        $this->validate($request, $rules);
        try {
            $mon->name = $request->name;
            $mon->periodicity = $request->periodicity;
            $mon->accumulation = ($request->accumulation === '0' ? false : true);
            $mon->album_id = $request->album;
            $mon->save();
            return ['message' => 'Запись сохранена. Id:' . $mon->id, 'id' => $mon->id, ];
        } catch (\Illuminate\Database\QueryException $e) {
            return($this->error_message($e->errorInfo[0]));
        }
    }

    public function destroy($monitoring)
    {
        $mon = Monitoring::find($monitoring);
        $docs = \App\Document::OfMonitoring($mon->id)->count();
        if ($docs > 0) {
            return ['error' => 422, 'message' => 'Мониторинг содержит отчетные документы. Удаление невозможно', ];
        }
        $mon->delete();
        return ['message' => 'Удален мониторинг Id' . $mon->id, 'id' => $mon->id,  ];
    }

    public function fetchList()
    {
        //$m = Monitoring::orderBy('name')->get();
        //var_dump($m);
        //dd($m);
        return Monitoring::with('album')->with('periodicities')->orderBy('name')->get();
    }

    protected function error_message($errorCode)
    {
        switch ($errorCode) {
            case '23505':
                $message = 'Запись не сохранена. Дублирующиеся значения.';
                break;
            default:
                $message = 'Запись не сохранена. Код ошибки ' . $errorCode . '.';
                break;
        }
        return ['error' => 422, 'message' => $message];
    }

    protected function validateRules()
    {
        return [
            'name' => 'required|max:256|unique:monitorings',
            'periodicity' => 'required|integer|exists:dic_periodicities,code',
            'accumulation' => 'required|in:1,0',
            'album' => 'required|integer|exists:albums,id',
        ];
    }

}

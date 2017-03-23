<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Period;
use App\Document;

class PeriodAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('jqxadmin.periods');
    }

    public function fetchPeriods()
    {
        return Period::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'name' => 'required|unique:periods',
                'begin_date' => 'required|date',
                'end_date' => 'required|date|after:begin_date',
                'pattern_id' => 'exists:period_patterns,id',
            ]
        );
        try {
            $newperiod = Period::create($request->all());
            return ['message' => 'Новая запись создана. Id:' . $newperiod->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == 7){
                return ['error' => 422, 'message' => 'Новая запись не создана. Существует Период с такими же датами начала и окончания.'];
            }
        }
    }

    public function update(Request $request)
    {
        $this->validate($request, [
                'name' => 'required',
                'begin_date' => 'required|date',
                'end_date' => 'required|date|after:begin_date',
                'pattern_id' => 'exists:period_patterns,id',
            ]
        );
        $period = Period::find($request->id);
        $period->name = $request->name;
        $period->begin_date = $request->begin_date;
        $period->end_date = $request->end_date;
        $period->pattern_id = $request->pattern_id;
        $period->medinfo_id = $request->medinfo_id;
        $result = [];
        try {
            $period->save();
            $result = ['message' => 'Запись id ' . $period->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == 7){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование имени/дат отчетного периода.'];
            }
        }
        return $result;
    }

    public function delete(Period $period)
    {
        $id = $period->id;
        $doc_count = Document::countInPeriod($id);
        if ($doc_count == 0) {
            $period->delete();
            return ['message' => 'Удален отчетный период Id ' . $id ];
        } else {
            return ['error' => 422, 'message' => 'Период Id ' . $id . ' содержит документы. Удаление невозможно.' ];
        }
    }
}

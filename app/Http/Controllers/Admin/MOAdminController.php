<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Unit;
use App\Document;
use App\DicUnitType;
use PhpParser\Comment\Doc;

class MOAdminController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $unit_types = DicUnitType::all(['code', 'name']);
        $aggregate_units = Unit::MayBeAggregate()->orderBy('unit_name')->get(['id', 'unit_name']);
        return view('jqxadmin.units', compact('unit_types', 'aggregate_units'));
    }

    public function fetchUnits()
    {
        return Unit::orderBy('unit_code')->with('parent')->get();
    }

    public function unitStore(Request $request)
    {
        $this->validate($request, [
                'parent_id' => 'required|exists:mo_hierarchy,id',
                'unit_code' => 'required|max:32|unique:mo_hierarchy',
                'inn' => 'digits:10|unique:mo_hierarchy',
                'unit_name' => 'required|max:256|unique:mo_hierarchy',
                'node_type' => 'required|integer',
                'report' => 'required|in:1,0',
                'aggregate' => 'required|in:1,0',
                'blocked' => 'required|in:1,0',
                'medinfo_id' => 'integer',
            ]
        );
        $newunit = new Unit();
        $newunit->parent_id = $request->parent_id;
        $newunit->unit_code = $request->unit_code;
        $newunit->unit_name = preg_replace('/[\r\n\t]/', '', $request->unit_name);
        $newunit->inn =  empty($request->inn) ? null : $request->inn;
        $newunit->node_type = $request->node_type;
        $newunit->report = $request->report;
        $newunit->aggregate = $request->aggregate;
        $newunit->blocked = $request->blocked;
        $newunit->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        $newunit->save();
        try {
            $newunit->save();
            return ['message' => 'Новая запись создана. Id:' . $newunit->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирующиеся значения.';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
        }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function unitUpdate(Unit $unit, Request $request)
    {
        $this->validate($request, [
                //'id' => 'required|exists:mo_hierarchy',
                'parent_id' => 'required|exists:mo_hierarchy,id',
                'unit_code' => 'required|max:32',
                'inn' => 'digits:10',
                'unit_name' => 'required|max:256',
                'node_type' => 'required|integer',
                'report' => 'required|in:1,0',
                'aggregate' => 'required|in:1,0',
                'blocked' => 'required|in:1,0',
                'medinfo_id' => 'integer',
            ]
        );
        $unit->parent_id = $request->parent_id;
        $unit->unit_code = $request->unit_code;
        $unit->unit_name = preg_replace('/[\r\n\t]/', '', $request->unit_name);
        $unit->inn =  empty($request->inn) ? null : $request->inn;
        $unit->node_type = $request->node_type;
        $unit->report = $request->report;
        $unit->aggregate = $request->aggregate;
        $unit->blocked = $request->blocked;
        $unit->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        $result = [];
        try {
            $unit->save();
            $result = ['message' => 'Запись id ' . $unit->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == '23505'){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование данных.'];
            }
        }
        return $result;
    }

    public function unitDelete(Unit $unit)
    {
        $doc_count = Document::countInUnit($unit->id);
        if ($doc_count == 0) {
            $unit->delete();
            return ['message' => 'Удалена организационная единица Id' . $unit->id ];
        } else {
            return ['error' => 422, 'message' => 'БД содержит документы связанные с ОЕ Id' . $unit->id . '. Удаление невозможно.' ];
        }
    }

}

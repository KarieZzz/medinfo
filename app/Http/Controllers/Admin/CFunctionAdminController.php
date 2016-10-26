<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\CFunction;
use App\DicErrorLevel;

class CFunctionAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        $error_levels = DicErrorLevel::all(['code', 'name']);
        return view('jqxadmin.cfunctions', compact('forms', 'error_levels'));
    }

    public function fetchControlFunctions(int $table)
    {
        return CFunction::OfTable($table)->with('table')->get();
    }

    public function Store(Request $request)
    {
        $this->validate($request, $this->validateRules());
        $newfunction = new CFunction();
        $newfunction->table_id = $request->table_id;
        $newfunction->level = $request->level;
        $newfunction->script = $request->script;
        $newfunction->comment = $request->comment;
        $newfunction->blocked = $request->blocked;
        $newfunction->compiled = 0;

        //$newfunction->save();
        try {
            $newfunction->save();
            return ['message' => 'Новая запись создана. Id:' . $newfunction->id];
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


    public function Update(CFunction $cfunction, Request $request)
    {
        $this->validate($request, $this->validateRules());
        $cfunction->level = $request->level;
        $cfunction->script = $request->script;
        $cfunction->comment = $request->comment;
        $cfunction->blocked = $request->blocked;
        try {
            $cfunction->save();
            return ['message' => 'Запись id ' . $cfunction->id . ' сохранена'];
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

    public function Delete(CFunction $cfunction)
    {
        $cfunction->delete();
        return ['message' => 'Удалена функция Id' . $cfunction->id ];
    }

    protected function validateRules()
    {
        return [
            'table_id' => 'required|exists:tables,id',
            'level' => 'integer',
            'script' => 'required|max:512',
            'comment' => 'max:128',
            'blocked' => 'required|in:1,0',
        ];
    }

}

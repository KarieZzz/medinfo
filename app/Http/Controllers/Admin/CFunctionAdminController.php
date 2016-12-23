<?php

namespace App\Http\Controllers\Admin;

use App\Medinfo\Lexer\FunctionDispatcher;
use App\Table;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Form;
use App\CFunction;
use App\ControlCashe;
use App\DicErrorLevel;
use App\Medinfo\Lexer\ControlFunctionLexer;
use App\Medinfo\Lexer\ControlFunctionParser;
use App\Medinfo\Lexer\CompareControlInterpreter;


class CFunctionAdminController extends Controller
{
    //
    public $compile_error;
    public $functionIndex;

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
        return CFunction::OfTable($table)->orderBy('updated_at')->with('table')->get();
    }

    public function store(Table $table, Request $request)
    {
        $this->validate($request, $this->validateRules());
        $cache = $this->compile($request->script, $table);
        if (!$cache) {
            return ['error' => 422, 'message' => $this->compile_error];
        }
        $newfunction = new CFunction();
        $newfunction->table_id = $table->id;
        $newfunction->level = $request->level;
        $newfunction->script = $request->script;
        $newfunction->comment = $request->comment;
        $newfunction->blocked = $request->blocked;
        $newfunction->function = $this->functionIndex;
        $newfunction->compiled_cashe = $cache;
        //$newfunction->save();
        try {
            $newfunction->save();
            $deleted_protocols =  ControlCashe::where('table_id', $table->id)->delete();
            return ['message' => 'Новая запись создана. Id:' . $newfunction->id . 'Удалено кэшированных протоколов контроля: ' . $deleted_protocols];
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

    public function update(CFunction $cfunction, Request $request)
    {
        $this->validate($request, $this->validateRules());
        $table = Table::find($cfunction->table_id);
        $cfunction->level = $request->level;
        $cache = $this->compile($request->script, $table);
        if (!$cache) {
            return ['error' => 422, 'message' => $this->compile_error];
        }
        $cfunction->script = $request->script;
        $cfunction->comment = $request->comment;
        $cfunction->blocked = $request->blocked;
        $cfunction->function = $this->functionIndex;
        $cfunction->compiled_cashe = $cache;
        try {
            $cfunction->save();
            $deleted_protocols =  ControlCashe::where('table_id', $table->id)->delete();
            return ['message' => 'Запись id ' . $cfunction->id . ' сохранена.' . 'Удалено кэшированных протоколов контроля: ' . $deleted_protocols];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
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
    }

    public function compile($script, Table $table)
    {
        try {
            $lexer = new ControlFunctionLexer($script);
            $parser = new ControlFunctionParser($lexer);
            $r = $parser->run();
            $callInterpreter = FunctionDispatcher::INTERPRETERNS . FunctionDispatcher::$interpreterNames[$parser->functionIndex];
            $interpreter = new $callInterpreter($r, $table);
            //dd($interpreter);
            $compiled_cache = serialize($interpreter);
            $this->functionIndex = $parser->functionIndex;
            return $compiled_cache;
        } catch (\Exception $e) {
            $this->compile_error = "Ошибка при компилляции функции: " . $e->getMessage();
            return false;
        }
    }

    public function delete(CFunction $cfunction)
    {
        $cfunction->delete();
        $deleted_protocols =  ControlCashe::where('table_id', $cfunction->id)->delete();
        return ['message' => 'Удалена функция Id' . $cfunction->id . 'Удалено кэшированных протоколов контроля: ' . $deleted_protocols];
    }

    protected function validateRules()
    {
        return [
            'level'     => 'integer',
            'script'    => 'required|max:512',
            'comment'   => 'max:128',
            'blocked'   => 'required|in:1,0',
        ];
    }

}

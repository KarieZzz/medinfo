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

    public function store(Table $table, Request $request)
    {
        $this->validate($request, $this->validateRules());
        try {
            $interpreter =  new CompareControlInterpreter($this->compile($request->script), $table);
        } catch (\Exception $e) {
            return ['error' => 422, 'message' => "Ошибка при компилляции функции: " . $e->getMessage()];
        }
        $newfunction = new CFunction();
        $newfunction->table_id = $table->id;
        $newfunction->level = $request->level;
        $newfunction->script = $request->script;
        $newfunction->comment = $request->comment;
        $newfunction->blocked = $request->blocked;
        $newfunction->compiled_cashe = serialize($interpreter);
        //$newfunction->save();
        try {
            $newfunction->save();
            $deleted_protocols =  ControlCashe::where('table_id', $table->id)->delete();

            return ['message' => 'Новая запись создана. Id:' . $newfunction->id ];
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
        $deleted_protocols = 0;
        if ($cfunction->script !== $request->script) {
            try {
                $interpreter =  new CompareControlInterpreter($this->compile($request->script), $table);
                $cfunction->compiled_cashe = serialize($interpreter);
                $deleted_protocols =  ControlCashe::where('table_id', $table->id)->delete();
            } catch (\Exception $e) {
                return ['error' => 422, 'message' => "Ошибка при компилляции функции: " . $e->getMessage()];
            }
        }
        $cfunction->script = $request->script;
        $cfunction->comment = $request->comment;
        $cfunction->blocked = $request->blocked;
        try {

            $cfunction->save();
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

    public function compile($script)
    {
        $lexer = new ControlFunctionLexer($script);
        $parser = new ControlFunctionParser($lexer);
        return $parser->run();
    }

    public function delete(CFunction $cfunction)
    {
        $cfunction->delete();
        return ['message' => 'Удалена функция Id' . $cfunction->id ];
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

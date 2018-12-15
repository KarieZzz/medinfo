<?php

namespace App\Http\Controllers\Admin;

use App\Row;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Column;
use App\ColumnCalculation;

class CalculatedColumnAdminController extends Controller
{
    //
    public $lexer;
    public $parcer;

    public function index()
    {

    }

    public function show(Column $column)
    {
        $f = ColumnCalculation::OfColumn($column->id)->first();
        $data = [];
        if (is_null($f)) {
            $data['placeholder'] = 'введите функцию рaсчета';
            $data['formula'] = null;
        } else {
            $data['placeholder'] = '';
            $data['formula'] = $f->formula;
            $data['id'] = $f->id;
        }
        return $data;
    }

    public function store(Column $column, Request $request)
    {
        $this->validate($request, $this->validateRules());
        $newformula = new ColumnCalculation();
        $newformula->column_id = $column->id;
        $parced = $this->validateExpression($request->formula);
        if ($parced === true) {
            $newformula->formula = $request->formula;
            $newformula->compiled = $this->compileExpression($column);
        } else {
            return ['saved' => false, 'message' => $parced];
        }
        $newformula->comment = $request->comment;
        //$newformula->save();
        try {
            $newformula->save();
            return ['saved' => true, 'message' => 'Новая запись создана. Id:' . $newformula->id, 'id' => $newformula->id, ];
        } catch (\Illuminate\Database\QueryException $e) {
            return($this->error_message($e->errorInfo[0]));
        }
    }

    public function update(ColumnCalculation $columnCalculation, Request $request)
    {
        $this->validate($request, $this->validateRules());
        $parced = $this->validateExpression($request->formula);
        if ($parced === true) {
            $columnCalculation->formula = $request->formula;
            $column = Column::find($columnCalculation->column_id);
            $columnCalculation->compiled = $this->compileExpression($column);
        } else {
            return ['saved' => false, 'message' => $parced];
        }
        $columnCalculation->comment = $request->comment;
        //$columnCalculation->save();
        try {
            $columnCalculation->save();
            return ['saved' => true, 'message' => 'Изменения сохранены'];
        } catch (\Illuminate\Database\QueryException $e) {
            return($this->error_message($e->errorInfo[0]));
        }

    }

    protected function validateRules()
    {
        return [
            'formula' => 'required|max:512',
        ];
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

    public function validateExpression($input)
    {
        try {
            $this->lexer = new \App\Medinfo\DSL\CalculationFunctionLexer($input);
            $tokenstack = $this->lexer->getTokenStack();
            $this->parcer = new \App\Medinfo\DSL\CalculationFunctionParser($tokenstack);
            $this->parcer->expression();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    public function compileExpression(Column $column)
    {
        $stack = $this->lexer->celladressStack;
        $compiled = [];
        $compiled['vector'] = 'rows';
        $compiled['cellstack'] = [];
        $rows = Row::OfTable($column->table_id)->get();
        $i = 0;
        foreach ($rows as $row) {
            $stack->rewind();
            $compiled['cellstack']['el' . $i] = [];
            while ($stack->valid()) {
                $label = $stack->current();
                $columncode = mb_substr($label,1);
                $c = Column::OfTableColumnCode($column->table_id, $columncode)->first();
                $compiled['cellstack']['el' . $i][] = ['r' => $row->id, 'c' => $c->id];
                $stack->next();
            }
            $i++;
        }
        //$ret = json_encode($compiled, JSON_FORCE_OBJECT);
        //$ret = json_encode($compiled);
        //dd($ret);
        return json_encode($compiled);

    }
}

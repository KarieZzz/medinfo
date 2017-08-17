<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Column;
use App\ColumnCalculation;

class CalculatedColumnAdminController extends Controller
{
    //
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
            $lexer = new \App\Medinfo\Calculation\CalculationFunctionLexer($input);
            $tokenstack = $lexer->getTokenStack();
            $parcer = new \App\Medinfo\Calculation\CalculationFunctionParser($tokenstack);
            $parcer->expression();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

}

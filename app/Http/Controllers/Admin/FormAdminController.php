<?php

namespace App\Http\Controllers\Admin;

use App\Column;
use App\Row;
use Illuminate\Http\Request;

//use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\Document;

class FormAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $realforms = Form::Real()->get(['id', 'form_code', 'form_name']);
        return view('jqxadmin.forms', compact('realforms'));
    }

    public function fetchForms()
    {
        return Form::orderBy('form_index')->with('inheritFrom')->get();
        //return Form::all();
    }

    public function store(Request $request)
    {
        // TODO: Добавить проверку для кода формы -  допустимые символы: цифры, строчные кириллические буквы, точка, дефис
        $this->validate($request, $this->validateRules());
        try {
            $newform = Form::create(['form_index' => $request->form_index, 'form_name' => $request->form_name, 'form_code' => $request->form_code] );
            $newform->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
            $newform->short_ms_code = empty($request->short_ms_code) ? null : $request->short_ms_code;
            $newform->relation = empty($request->relation) ? null : (int)$request->relation;
            $newform->medstatnsk_id = empty($request->medstatnsk_id) ? null : (int)$request->medstatnsk_id;
            $newform->save();
            return [ 'message' => 'Новая запись создана. Id:' . $newform->id ];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            if($errorCode == '23505'){
                return ['error' => 422, 'message' => 'Новая запись не создана. Существует форма с таким же именем/кодом.'];
            }
        }
    }

    public function update(Request $request, Form $form)
    {
        $this->validate($request, $this->validateRules());
        //$form = Form::find($request->id);
        $form->form_index = $request->form_index;
        $form->form_code = $request->form_code;
        $form->form_name = $request->form_name;
        $form->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $form->short_ms_code = empty($request->short_ms_code) ? null : $request->short_ms_code;
        $form->relation = empty($request->relation) ? null : (int)$request->relation;
        $form->medstatnsk_id = empty($request->medstatnsk_id) ? null : (int)$request->medstatnsk_id;
        $result = [];
        try {
            $form->save();
            $result = ['message' => 'Запись id ' . $form->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            if($errorCode == '23505'){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование имени/кода формы.'];
            }
        }
        return $result;
    }

    public function delete(Form $form)
    {
        $form_code = $form->form_code;
        $doc_count = Document::countInForm($form->id);
        if ($doc_count == 0) {
            $tdeleted = 0;
            $rdeleted = 0;
            $cdeleted = 0;
            foreach ($form->tables()->get() as $table) {
                $rdeleted += Row::OfTable($table->id)->delete();
                $cdeleted += Column::OfTable($table->id)->delete();
                $tdeleted += $table->delete();
            }
            $form->delete();
            return ['message' => "Удалена форма $form_code, включая таблицы: $tdeleted, строки $rdeleted, графы $cdeleted"];
        } else {
            return ['error' => 422, 'message' => 'Форма ' . $form_code . ' содержит документы. Удаление невозможно.' ];
        }
    }

    protected function validateRules()
    {
        return [
            'form_index' => 'required|integer',
            'form_name' => 'required|max:256',
            'form_code' => 'required|max:7',
            'medstat_code' => 'digits:5',
            'relation' => 'integer',
            'short_ms_code' => 'required_with:medstat_code|max:5',
            'medstatnsk_id' => 'integer',
        ];
    }
}
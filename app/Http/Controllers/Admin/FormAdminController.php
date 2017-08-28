<?php

namespace App\Http\Controllers\Admin;

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
        return view('jqxadmin.forms');
    }

    public function fetchForms()
    {
        return Form::orderBy('form_index')->get();
        //return Form::all();
    }

    public function store(Request $request)
    {
        // TODO: Добавить проверку для кода формы -  допустимые символы: цифры, строчные кириллические буквы, точка, дефис
        $this->validate($request, [
                'group_id' => 'integer',
                'form_index' => 'integer',
                'form_name' => 'required|unique:forms|max:256',
                'form_code' => 'required|unique:forms|max:7',
                'medstat_code' => 'digits:5',
                'medinfo_id' => 'integer',
            ]
        );
        try {
            $newform = Form::create($request->all());
            return ['message' => 'Новая запись создана. Id:' . $newform->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == 7){
                return ['error' => 422, 'message' => 'Новая запись не создана. Существует форма с таким же именем/кодом.'];
            }
        }
    }

    public function update(Request $request)
    {
        $this->validate($request, [
                'group_id' => 'integer',
                'form_index' => 'integer',
                'form_name' => 'required|max:256',
                'form_code' => 'required|max:7',
                'medstat_code' => 'digits:5',
                'medinfo_id' => 'integer',
            ]
        );
        $form = Form::find($request->id);
        $form->group_id = $request->group_id;
        $form->form_index = $request->form_index;
        $form->form_code = $request->form_code;
        $form->form_name = $request->form_name;
        $form->file_name = $request->file_name;
        $form->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $form->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        $result = [];
        try {
            $form->save();
            $result = ['message' => 'Запись id ' . $form->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == 7){
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
            $form->delete();
            return ['message' => 'Удалена форма ' . $form_code ];
        } else {
            return ['error' => 422, 'message' => 'Форма ' . $form_code . ' содержит документы. Удаление невозможно.' ];
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\FormSection;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;

class FormSectionAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $forms = Form::orderBy('form_code')->get();
        return view('jqxadmin.formsections', compact('forms'));
    }

    public function fetch_formsections()
    {
        return FormSection::with('form')->orderBy('form_id')->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules());
        $maxindex = FormSection::OfForm($request->form)->max('section_index');
        $newsection = new FormSection();
        $newsection->form_id = $request->form;
        $newsection->section_name = $request->section;
        $newsection->section_index = $maxindex + 1;
        //$newsection->save();
        try {
            $newsection->save();
            return ['message' => 'Новая запись создана. Id:' . $newsection->id, 'id' => $newsection->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирующиеся значения (' . $errorCode . ').';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function update($fs, Request $request)
    {
        $this->validate($request, $this->validateRules());
        $section = FormSection::find($fs);
        $section->form_id = $request->form;
        $section->section_name = $request->section;
        try {
            $section->save();
            return ['message' => 'Изменения сохранены. Id:' . $section->id, 'id' => $section->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Изменения не сохранены. Дублирующиеся значения (' . $errorCode . ').';
                    break;
                default:
                    $message = 'Изменения не сохранены. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }

    }

    public function editSection($fs)
    {
        $s = FormSection::find($fs);
        dd($s);
    }

    protected function validateRules()
    {
        return [
            'section' => 'required|max:256',
            'form' => 'required|integer|exists:forms,id',
            'section_index'  => 'integer',
        ];
    }

}

<?php

namespace App\Http\Controllers\Admin;

use App\Album;
use App\AlbumFormsection;
use App\FormSection;
use App\FormSectionTable;
use App\Table;
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
        $albums = Album::orderBy('album_name')->get();
        return view('jqxadmin.formsections', compact('forms', 'albums'));
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
            if ($request->include === '1' ) {
                $proccessed = AlbumFormsection::firstOrCreate([ 'album_id' => $request->album, 'formsection_id' => $newsection->id ]);
            }
            return ['message' => 'Новая запись создана. Id:' . $newsection->id . '. Включена в альбом Id:' . $request->album , 'id' => $newsection->id];
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

    public function destroy($fs)
    {
        $proccessed_album = AlbumFormsection::OfFormSection($fs)->delete();
        $proccessed_table = FormSectionTable::OfFormSection($fs)->delete();
        $section_deleted = FormSection::destroy($fs);
        return ['message' => 'Удален раздел Id:' . $fs . ' и свзяанные записи в альбоме разделов, перечне таблиц'];
    }

    public function editSection($fs)
    {
        $formsection = FormSection::find($fs);
        $included_tables = FormSectionTable::OfFormSection($formsection->id)->get();
        $tables = Table::OfForm($formsection->form->id)->orderBy('table_index')->get();
        return view('jqxadmin.formsectionset', compact('formsection', 'tables', 'included_tables'));
    }

    public function updateSectionSet($fs, Request $request)
    {
        $tables = explode(',', $request->tables);
        $currents = FormSectionTable::OfFormSection($fs)->get();
        $destroyed = 0;
        $included = 0;
        foreach ( $currents as $current) {
            if (!in_array($current->table_id, $tables)) {
                FormSectionTable::destroy($current->id);
                $destroyed++;
            }
        };
        foreach ($tables as $table) {
            $newrec = FormSectionTable::firstOrNew(['formsection_id' => $fs, 'table_id' => $table]);
            $newrec->save();
            $included++;
        }
        return ['message' => 'Изменения сохранены', 'destroyed' => $destroyed , 'included' => $included ];

    }

    protected function validateRules()
    {
        return [
            'section' => 'required|max:256',
            'form' => 'required|integer|exists:forms,id',
            'album' => 'required|integer|exists:albums,id',
            'section_index'  => 'integer',
            'include' => 'required|in:1,0',
        ];
    }

}

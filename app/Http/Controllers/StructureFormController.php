<?php

namespace App\Http\Controllers;

use App\Medinfo\FormMM;
use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Form;

//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Session;
//use Symfony\Component\DomCrawler\Form;

class StructureFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {


        //$forms = Forms::paginate(10);
        //$forms = Forms::simplePaginate(10);
        //$forms = Forms::all()->sortBy('form_code')->forPage(3,10);
        $forms = DB::table('forms')->orderBy('form_code')->paginate(10);

        //dd($forms);
        //$forms = new FormClass(2);
        return view('structure.forms', compact('forms'));
        //return $forms;

    }

    public function edit(Form $form)
    {
        return view('structure.editform', compact('form'));
    }

    public function newform()
    {
        return view('structure.newform');
    }

    public function update(Request $request, Form $form)
    {
        $this->validate($request, [
                'form_name' => 'required|max:256',
                'form_code' => 'required',
                'medstat_code' => 'min:2',
            ]
        );
        $form->update($request->all());

        \Session::flash('flash_message', 'Запись сохранена');
        return back();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'form_name' => 'required|max:256',
                'form_code' => 'required',
                'medstat_code' => 'min:2',
            ]
        );
        $form = new Form;
        $form->form_name = $request->form_name;
        $form->form_code = $request->form_code;
        $form->medstat_code = $request->medstat_code;
        $form->medinfo_id = $request->medinfo_id;
        $form->file_name = $request->file_name;
        $form->save();
        \Session::flash('flash_message', 'Запись сохранена');
        return back();
        //return $request->all();
    }

    public function testQuery()
    {
        $query = "SELECT * FROM forms WHERE id = 9999";
        $forms = DB::selectOne($query);
        $f = new Form();
        $out= $f->all()->toJson();
        return $out;
        //var_dump($forms);
        /*if (!$forms) {
            return 'Нет формы с указанным Id';
        } else {
            return $forms->form_name;
        }*/

    }

}

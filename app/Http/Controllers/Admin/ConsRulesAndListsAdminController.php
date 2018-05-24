<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConsRulesAndListsAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $forms = \App\Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.set_consrules_and_lists', compact('forms'));
    }

    public function applyRule(Request $request)
    {

    }

    public function applyList(Request $request)
    {

    }

}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;

class BriefReferenceMaker extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compose_query()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('reports.composequickquery', compact('forms'));
    }

}

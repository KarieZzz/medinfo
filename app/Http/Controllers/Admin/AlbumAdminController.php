<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AlbumAdminController extends Controller
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
}

<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class FormTablePickerController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('analytics');
    }

    public function fetchTables(int $form)
    {
        return \App\Table::OfForm($form)->orderBy('table_index')->with('form')->get();
    }
}

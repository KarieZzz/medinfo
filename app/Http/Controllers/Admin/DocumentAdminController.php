<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DocumentAdminController extends Controller
{
    //
    public function index()
    {
        return view('jqxadmin.documents');
    }

    public function fetchDocuments($unit)
    {

    }
}

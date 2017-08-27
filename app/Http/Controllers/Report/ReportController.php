<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('analytics');
    }

    public function index()
    {
        echo "Работает";
        //return view('reports.composequickquery');
    }
}

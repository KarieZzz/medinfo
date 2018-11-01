<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DocInfoController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function getDocInfo(int $document)
    {
        $records = \App\ValuechangingLog::OfDocument($document)->orderBy('occured_at', 'desc')
            ->with('worker')
            ->with('table')
            ->with('row')
            ->with('column')
            ->take(30)->get();

        return compact('records');
    }
}

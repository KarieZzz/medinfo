<?php

namespace App\Http\Controllers\Admin;

use App\Document;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ValueChangingAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function showFormEditingLog(Document $document)
    {
        $records = \App\ValuechangingLog::OfDocument($document->id)->get();
        $form = $document->form()->first();
        $unit = $document->unit()->first();
        $period = $document->period()->first();
        //dd($records[0]->column);
        //return $form->id;
        return view('reports.valuechangelog',  compact('form', 'unit', 'period', 'records') );
    }
}

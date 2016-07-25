<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\GenericUser;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Unit;
use App\Form;
use App\Medinfo\PeriodMM;
use App\Document;
use App\Table;
use App\NECells;
use App\Cell;

class AggregatesDashboardController extends DashboardController
{
    //
    public function index(Request $request)
    {
        $worker = Auth::guard('datainput')->user();
        $document = Document::find($request->id);
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $editpermission = $this->isEditPermission($worker->permission, 0);
        $editpermission ? $editmode = 'Редактирование' : $editmode = 'Только чтение';
        $period_id = $document->period_id;
        $period = PeriodMM::getPeriodFromId($period_id);
        $editedtables = $this->getEditedTables($document->id);
        $noteditablecells = NECells::where('f', $form->id)->select('t', 'r', 'c')->get();



        return view('jqxdatainput.aggregatedashboard', compact(
            'current_unit', 'document', 'worker', 'statelabel', 'editpermission', 'editmode',
            'form', 'period', 'editedtables', 'noteditablecells', 'forformtable', 'renderingtabledata',
            'laststate'
        ));
    }
}

<?php

namespace App\Http\Controllers\StatDataInput;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\GenericUser;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Unit;
use App\Form;
use App\Document;
use App\Table;
use App\NECells;
use App\Cell;

class AggregatesDashboardController extends DashboardController
{
    //
    public function index(Document $document)
    {
        $worker = Auth::guard('datainput')->user();
        $statelabel = Document::$state_labels[$document->state];
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $editpermission = $this->isEditPermission($worker->permission, 0);
        $editpermission ? $editmode = 'Редактирование' : $editmode = 'Только чтение';
        $period = Period::find($document->period_id);
        $editedtables = $this->getEditedTables($document->id);
        $noteditablecells = NECells::where('f', $form->id)->select('t', 'r', 'c')->get();
        $renderingtabledata = $this->composeDataForTablesRendering($form, $editedtables);
        $laststate = $this->getLastState($worker, $document, $form);
        return view('jqxdatainput.aggregatedashboard', compact(
            'current_unit', 'document', 'worker', 'statelabel', 'editpermission', 'editmode',
            'form', 'period', 'editedtables', 'noteditablecells', 'forformtable', 'renderingtabledata',
            'laststate'
        ));
    }

    public function aggregateData(Document $document)
    {
        $result = [];
        // перед сведением данных удаление старых данных
        $affectedcells = Cell::where('doc_id', $document->id)->delete();
        $units = Unit::getDescendants($document->ou_id);
        $included_documents = Document::whereIn('ou_id', $units)
            ->where('dtype', 1)
            ->where('form_id', $document->form_id)
            ->where('period_id', $document->period_id)
            ->pluck('id');
        $strigified_documents = implode(',', $included_documents->toArray());
        $now = Carbon::now();
        $query = "INSERT INTO statdata
            (doc_id, table_id, row_id, col_id, value, created_at, updated_at )
          SELECT '{$document->id}', v.table_id, v.row_id, v.col_id, SUM(value), '$now', '$now'  FROM statdata v
            JOIN documents d on v.doc_id = d.id
            JOIN tables t on (v.table_id = t.id)
            JOIN forms f on d.form_id = f.id
            JOIN mo_hierarchy h on d.ou_id = h.id
          WHERE d.id in ({$strigified_documents}) AND h.blocked <> 1 GROUP BY v.table_id, v.row_id, v.col_id";
        $affected_cells = \DB::select($query);
        $result['affected_cells'] = count($affected_cells);
        return $result;
    }
}

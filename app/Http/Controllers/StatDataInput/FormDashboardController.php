<?php

namespace App\Http\Controllers\StatDataInput;

//use App\Worker;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
//use App\Http\Controllers\Controller;
use App\Unit;
use App\Period;
use App\Form;
//use App\Medinfo\PeriodMM;
use App\Document;
use App\Table;
use App\NECells;
use App\Cell;
use App\ValuechangingLog;
use App\Medinfo\TableControlMM;

class FormDashboardController extends DashboardController
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function index(Document $document)
    {
        $worker = Auth::guard('datainput')->user();
        $statelabel = Document::$state_labels[$document->state];
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $editpermission = $this->isEditPermission($worker->permission, $document->state);
        $editpermission ? $editmode = 'Редактирование' : $editmode = 'Только чтение';
        $period = Period::find($document->period_id);
        $editedtables = $this->getEditedTables($document->id);
        $noteditablecells = NECells::where('f', $form->id)->select('t', 'r', 'c')->get();
        $renderingtabledata = $this->composeDataForTablesRendering($form, $editedtables);
        $laststate = $this->getLastState($worker, $document, $form);
        //return $datafortables;
        //return $renderingtabledata;
        return view('jqxdatainput.formdashboard', compact(
            'current_unit', 'document', 'worker', 'statelabel', 'editpermission', 'editmode',
            'form', 'period', 'editedtables', 'noteditablecells', 'forformtable', 'renderingtabledata',
            'laststate'
        ));
    }



    public function fetchValues(int $document, Table $table)
    {
        $rows = $table->rows->where('deleted', 0)->sortBy('row_index');
        $cols = $table->columns->where('deleted', 0)->sortBy('column_index');
        $data = array();
        $i=0;
        foreach ($rows as $r) {
            $row = array();
            $row['id'] = $r->id;
            foreach($cols as $col) {
                $contentType = $col->getMedinfoContentType();
                if ($contentType == 'header') {
                    if ($col->column_index == 1) {
                        $row[$col->id] = $r->row_name;
                    } elseif ($col->column_index == 2) {
                        $row[$col->id] = $r->row_code;
                    }
                } elseif ($contentType == 'data') {
                    if ($c = Cell::OfDTRC($document, $table->id, $r->id, $col->id)->first()) {
                        //$row[$col->id] = is_null($c->value) ? '' : number_format($c->value, $col->decimal_count, '.', '') ;
                        $row[$col->id] = is_null($c->value) ? null : number_format($c->value, $col->decimal_count, '.', '');
                    }
                }
                //elseif ( $contentType == 'comment') {
                  //  if (isset($r['add_inf'][$col['col_id']])) {
                    //    $row[$col['col_id']] = $r['add_inf'][$col['col_id']]['row_comment'];
                    //}
                //}
            }
            $data[$i] = $row;
            $i++;
        }
        return $data;
    }

    public function saveValue(Request $request, $document, $table)
    {
        $worker = Auth::guard('datainput')->user();
        $document = Document::find($document);
        $editpermission = $this->isEditPermission($worker->permission, $document->state);
        if ($editpermission) {
            $ou = $document->ou_id;
            $f = $document->form_id;
            $p = $document->period_id;
            $row = $request->row;
            $col = $request->column;
            $new = $request->value;
            $old = $request->oldvalue;
            $casted_new_value = (float)$new;
            $casted_old_value = (float)$old;

            if ($casted_new_value === $casted_old_value) {
                $data['cell_affected'] = false;
                $data['comment'] = "Изменения не сохранены по причине того что старое и новое значение равны, либо по причине того, что значение null изменено на 0 (или наоборот).";
            }
            else {
                $cell = Cell::firstOrCreate(['doc_id' => $document->id, 'table_id' => $table, 'row_id' => $row, 'col_id' => $col]);
                if (is_numeric($new)) {
                    if ($new == 0) {
                        //echo "Полученное значение 0, в БД записано null";
                        $cell->value = null;
                    }
                    else {
                        //echo "Получено значение отличное от нуля, в БД записано числовое значение";
                        $cell->value = $new;
                    }
                }
                else {
                    //echo "Получено нечисловое значение, в БД записано null";
                    $cell->value = null;
                }
                $result = $cell->save();
                if ($result) {
                    $data['cell_affected'] = true;
                    //$cell_adr = 'O' . $ou . 'F' . $f . 'T' . $table . 'R' . $row . 'C' . $col . 'P' . $p ;
                    $log = [
                        'worker_id' => $worker->id,
                        'oldvalue' => $casted_old_value,
                        'newvalue' => $casted_new_value,
                        'd' => $document->id,
                        'o' => $ou,
                        'f' => $f,
                        't' => $table,
                        'r' => $row,
                        'c' => $col,
                        'p' => $p,
                        'occured_at' => Carbon::now()
                    ];
                    $event = ValuechangingLog::create($log);
                    $data['event_id'] = $event->id;
                    // TODO: Решить нужно ли контролировать значения по мере изменения ячеек
                    //$v = new ValidateCellByMi($cell_adr);
                    //$cell_check_res = $v->checkoutCell();
                    //if ($cell_check_res !== null) {
                        //$data['protocol'] = $v->getProtocol();
                        //$data['valid'] = $cell_check_res;
                    //}
                }
                else {
                    $data['cell_affected'] = false;
                    $data['comment'] = "Ошибка сохранения данных на стороне сервера";
                }
            }
        }
        else {
            abort(1001, "Отсутствуют права для изменения данных в этом документе");
            //$data['cell_affected'] = false;
            //$data['error'] = 1001;
            //$data['comment'] = "Отсутствуют права для изменения данных в этом документе";
        }
        return $data;
    }

    public function fullValueChangeLog($document)
    {
        $document = Document::find($document);
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $period = Period::find($document->period_id);
        $values = ValuechangingLog::where('d', $document->id)->orderBy('occured_at', 'desc')
            ->with('worker')
            ->with('table')
            ->get();
        return view('jqxdatainput.fullvaluelog', compact('values', 'document', 'form', 'current_unit', 'period'));
    }

    public function tableControl(int $document, int $table)
    {
        if (TableControlMM::tableContainsData($document, $table)) {
            $control = new TableControlMM($document, $table);

            $table_protocol = $control->takeAllBatchControls();
            $table_protocol['no_data'] = false;
            return $table_protocol;
        }
        $table_protocol['no_data'] = true;
        return $table_protocol;
    }

    public function formControl(int $document)
    {
        $form_protocol = [];
        $form_protocol['valid'] = true;
        $form_protocol['no_data'] = true;
        $form_id = Document::find($document)->form_id;
        $tables = \DB::table('tables')
            ->where('form_id', $form_id)
            ->where('deleted', 0)
            ->where('medinfo_id', '<>', 0)
            ->orderBy('table_code')->get();
        foreach ($tables as $table) {
            if (TableControlMM::tableContainsData($document, $table->id)) {
                $offset = $table->table_code;
                $control = new TableControlMM($document, $table->id);
                $form_protocol[$offset] = $control->takeAllBatchControls();
                $form_protocol['valid'] = $form_protocol['valid'] && $form_protocol[$offset]['valid'];
                $form_protocol['no_data'] = $form_protocol['no_data'] && false;
            }
        }
        return $form_protocol;
    }

    public function formtest(Request $request)
    {
        $form = Form::find($request->id);
        //$forms = Form::all();
        //$tables = $form->tables->where('deleted', 0);
        $tables = $form->tables->orderBy('table_code');
/*        $cols = $tables[0]->columns->where('deleted', 0);
        foreach ($cols as $col) {
            $contentType = $col->getMedinfoContentType();
            //echo $contentType;
        }
        return $cols;*/
        return $tables;
    }

}

<?php

namespace App\Http\Controllers\StatDataInput;

//use App\Worker;
use Carbon\Carbon;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Unit;
use App\Form;
use App\Medinfo\PeriodMM;
use App\Document;
use App\Table;
use App\NECells;
use App\Cell;
use App\ValuechangingLog;


class FormDashboardController extends DashboardController
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function index(Request $request)
    {
        $worker = Auth::guard('datainput')->user();
        $document = Document::find($request->id);
        $statelabel = Document::$state_labels[$document->state];
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $editpermission = $this->isEditPermission($worker->permission, $document->state);
        $editpermission ? $editmode = 'Редактирование' : $editmode = 'Только чтение';
        $period_id = $document->period_id;
        $period = PeriodMM::getPeriodFromId($period_id);
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

    //Описательная информация для построения гридов динамически
    // возвращается json объект в формате для jqxgrid
    /**
     * @param Form $form
     * @param array $editedtables
     * @return mixed
     */
    protected function composeDataForTablesRendering(Form $form, array $editedtables)
    {
        $tables = Table::where('form_id', $form->id)->where('deleted', 0)->orderBy('table_code')->get();
        $forformtable = array();
        $datafortables = array();
        foreach ($tables as $table) {
            in_array($table->id, $editedtables) ? $edited = 1 : $edited = 0;
            $forformtable[] = "{ id: " . $table->id . ", code: '" . $table->table_code . "', name: '" . $table->table_name . "', edited: " . $edited . " }";
            $datafields_arr = array();
            $columns_arr = array();
            $datafields_arr[0] = array('name'  => 'id');
            $columns_arr[0] = array(
                'text'  => 'id',
                'dataField' => 'id',
                'width' => 0,
                'cellsalign' => 'left',
                'hidden' => true,
                'pinned' => true
            );
            $column_groups_arr = array();

            $cols = $table->columns->where('deleted', 0);
            foreach ($cols as $col) {
                $datafields_arr[] = array('name'  => $col->id);
                $width = $col->medinfo_size * 10;
                $decimal_count = $col->decimal_count;
                $number_count = $col->number_count;
                $contentType = $col->getMedinfoContentType();
                if ($contentType == 'data') {
                    $columns_arr[] = array(
                        'text'  => $col->column_index,
                        'dataField' => $col->id,
                        'width' => $width,
                        'cellsalign' => 'right',
                        'align' => 'center',
                        'cellsformat' => 'd' . $decimal_count,
                        'columntype' => 'numberinput',
                        'columngroup' => $col->id,
                        'filtertype' => 'number',
                        'cellclassname' => 'cellclass',
                        'cellbeginedit' => 'cellbeginedit',
                        'validation' => "e = function (cell, value) { if (value < 0) { return { result: false, message: 'Допускаются только положительные значения' }; } return true;};",
                        'createeditor' => "e = function(row, cellvalue, editor) { editor.jqxNumberInput({ digits: $number_count, decimalDigits: $decimal_count, min: 0, spinButtons: true, groupSeparator: '', inputMode: 'simple' })};"
                    );
                    $column_groups_arr[] = array(
                        'text' => $col->column_name,
                        'align' => 'center',
                        'name' => $col->id,
                        'rendered' => 'tooltiprenderer'
                    );
                } else if ($contentType == 'header') {
                    $columns_arr[] = array(
                        'text' => $col->column_name,
                        'dataField' => $col->id,
                        'width' => $width,
                        'cellsalign' => 'left',
                        'align' => 'center',
                        'pinned' => true,
                        'editable' => false,
                        'filtertype' => 'textbox'
                    );
                }
            }
            $datafortables[$table->id]['tablecode'] = $table->table_code;
            $datafortables[$table->id]['tablename'] = $table->table_name;
            $datafortables[$table->id]['datafields'] = $datafields_arr;
            $datafortables[$table->id]['columns'] = $columns_arr;
            $datafortables[$table->id]['columngroups'] = $column_groups_arr;
        }
        $datafortables_json = addslashes(json_encode($datafortables));

        $composedata['tablelist'] = $forformtable;
        $composedata['tablecompose'] = $datafortables_json;
        //$composedata['tablecompose'] = $datafortables;
        return $composedata;
    }

    // TODO: Доработать сохранение настроек редактирования отчета (таблица, фильтры, ширина колонок и т.д.)
    protected function getLastState(GenericUser $worker, Document $document, Form $form)
    {
        $laststate = array();
        $current_table = $form->tables->where('deleted', 0)->first();
        $laststate['currenttable'] = $current_table->id;
        return $laststate;
    }

    public function fetchValues(int $document, int $table)
    {
        $t = Table::find($table);
        $rows = $t->rows->where('deleted', 0)->sortBy('row_index');
        $cols = $t->columns->where('deleted', 0);
        $data = array();
        $i=0;
        foreach ($rows as $r) {
            $row = array();
            $row['id'] = $r->id;
            foreach($cols as $col) {
                $contentType = $col->getMedinfoContentType();
                if ( $contentType == 'header') {
                    if ($col->column_index == 1) {
                        $row[$col->id] = $r->row_name;
                    } elseif ($col->column_index == 2) {
                        $row[$col->id] = $r->row_code;
                    }
                } elseif ( $contentType == 'data') {
                    if ($c = Cell::OfDTRC($document, $t->id, $r->id, $col->id)->first()) {
                        $row[$col->id] = is_null($c->value) ? '' : number_format($c->value, $col->decimal_count, '.', '') ;
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
            $data['cell_affected'] = false;
            $data['error'] = 1001;
            $data['comment'] = "Отсутствуют права для изменения данных в этом документе";
        }
        return $data;
    }

    public function fullValueChangeLog($document)
    {
        $document = Document::find($document);
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $period = PeriodMM::getPeriodFromId($document->period_id);
        $values = ValuechangingLog::where('d', $document->id)->orderBy('occured_at', 'desc')
            ->with('worker')
            ->with('table')
            ->get();
        return view('jqxdatainput.fullvaluelog', compact('values', 'document', 'form', 'current_unit', 'period'));
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

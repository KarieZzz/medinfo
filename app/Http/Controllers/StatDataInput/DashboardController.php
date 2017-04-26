<?php

namespace App\Http\Controllers\StatDataInput;

use App\UnitGroup;
use Illuminate\Http\Request;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Unit;
use App\Period;
use App\Document;
use App\Album;
use App\Form;
use App\Table;
use App\Column;
use App\Row;
use App\Cell;
use App\NECellsFetch;
use App\ValuechangingLog;
use App\Medinfo\TableControlMM;
use App\Medinfo\ControlHelper;
use App\Medinfo\TableEditing;

class DashboardController extends Controller
{
    public $default_album;

    //
    public function index(Document $document)
    {
        $worker = Auth::guard('datainput')->user();
        $default_album = Album::Default()->first(['id']);
        if (!$default_album) {
            $default_album = Album::find(config('app.default_album'));
        }
        $statelabel = Document::$state_labels[$document->state];
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        if (!$current_unit) {
            $current_unit = UnitGroup::find($document->ou_id);
        }
        $editpermission = $this->isEditPermission($worker->permission, $document->state);
        $editpermission ? $editmode = 'Редактирование' : $editmode = 'Только чтение';
        $period = Period::find($document->period_id);
        $editedtables = Table::editedTables($document->id, $default_album->id);
        //dd($editedtables);
        //$noteditablecells = NECellsFetch::where('f', $form->id)->select('t', 'r', 'c')->get();
        $noteditablecells = NECellsFetch::byOuId($current_unit->id, $form->id);
        //dd($noteditablecells );
        $renderingtabledata = $this->composeDataForTablesRendering($form, $editedtables, $default_album);
        $laststate = $this->getLastState($worker, $document, $form, $default_album);
        //return $datafortables;
        //return $renderingtabledata;
        return view($this->dashboardView(), compact(
            'current_unit', 'document', 'worker', 'default_album', 'statelabel', 'editpermission', 'editmode',
            'form', 'period', 'editedtables', 'noteditablecells', 'forformtable', 'renderingtabledata',
            'laststate'
        ));
    }

    public function dashboardView()
    {
        return property_exists($this, 'dashboardView') ? $this->dashboardView : 'jqxdatainput.formdashboard';
    }

    /**
     * @param int $permission
     * @param int $document_state
     * @return bool
     */
    protected function isEditPermission(int $permission, int $document_state)
    {
        switch (true) {
            case (($permission & config('app.permission.permission_edit_report')) && ($document_state == 2 || $document_state == 16)) :
                $edit_permission = true;
                break;
            case (($permission & config('app.permission.permission_edit_prepared_report')) && $document_state == 4) :
                $edit_permission = true;
                break;
            case (($permission & config('app.permission.permission_edit_accepted_report')) && $document_state == 8) :
                $edit_permission = true;
                break;
            case (($permission & config('app.permission.permission_edit_approved_report')) && $document_state == 32) :
                $edit_permission = true;
                break;
            case (($permission & config('app.permission.permission_edit_aggregated_report')) && $document_state == 0) :
                $edit_permission = true;
                break;
            default:

                $edit_permission = false;
        }
        return $edit_permission;
    }

    //Описательная информация для построения гридов динамически
    // возвращается json объект в формате для jqxgrid
    /**
     * @param Form $form
     * @param array $editedtables
     * @return mixed
     */
    protected function composeDataForTablesRendering(Form $form, array $editedtables, Album $default_album)
    {
        //$tables = Table::where('form_id', $form->id)->where('deleted', 0)->orderBy('table_code')->get();
        $tables = Table::OfForm($form->id)->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album->id);
        })->get();
        //dd($tables);
        $forformtable = [];
        $datafortables = [];
        foreach ($tables as $table) {
            in_array($table->id, $editedtables) ? $edited = 1 : $edited = 0;
            // данные для таблицы-фильтра для навигации по отчетным таблицам в форме
            $forformtable[] = "{ id: " . $table->id . ", code: '" . $table->table_code . "', name: '" . $table->table_name . "', edited: " . $edited . " }";
            $datafortables[$table->id] = TableEditing::fetchDataForTableRenedering($table, $default_album);
        }
        $datafortables_json = addslashes(json_encode($datafortables));
        $composedata['tablelist'] = $forformtable;
        $composedata['tablecompose'] = $datafortables_json;
        //$composedata['tablecompose'] = $datafortables;
        return $composedata;
    }

    public function fetchValues(int $document, int $album, Table $table)
    {
        //$rows = $table->rows->where('deleted', 0)->sortBy('row_index');
        $rows = Row::OfTable($table->id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->get();
        //$cols = $table->columns->where('deleted', 0)->sortBy('column_index');
        $cols = Column::OfTable($table->id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->get();
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
                        //$row[$col->id] = is_null($c->value) ? null : number_format($c->value, $col->decimal_count, '.', '');
                        $row[$col->id] = is_null($c->value) ? null : number_format($c->value, $col->decimal_count, ',', '');
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
            dd($new);
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
        if (ControlHelper::tableContainsData($document, $table)) {
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
            if (ControlHelper::tableContainsData($document, $table->id)) {
                $offset = $table->table_code;
                $control = new TableControlMM($document, $table->id);
                $form_protocol[$offset] = $control->takeAllBatchControls();
                $form_protocol['valid'] = $form_protocol['valid'] && $form_protocol[$offset]['valid'];
                $form_protocol['no_data'] = $form_protocol['no_data'] && false;
            }
        }
        return $form_protocol;
    }

    // TODO: Доработать сохранение настроек редактирования отчета (таблица, фильтры, ширина колонок и т.д.)
    protected function getLastState(GenericUser $worker, Document $document, Form $form, $default_album)
    {
        $laststate = array();
        $current_table = Table::OfForm($form->id)->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album->id)->orderBy('table_code');
        })->first();
        //$current_table = $form->tables->where('deleted', 0)->sortBy('table_code')->first();
        $laststate['currenttable'] = $current_table;
        return $laststate;
    }
}

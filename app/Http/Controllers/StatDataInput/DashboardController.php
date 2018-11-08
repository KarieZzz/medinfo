<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Unit;
use App\Monitoring;
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
use App\FormSection;
use App\UnitList;
use App\Medinfo\TableEditing;

class DashboardController extends Controller
{
    //public $default_album;

    //
    public function index(Document $document)
    {
        $worker = Auth::guard('datainput')->user();
        //$album = Album::Default()->first(['id']);
        $album = Album::find($document->album_id);
        if (!$album) {
            $album = Album::find(config('medinfo.default_album'));
        }
        $statelabel = Document::$state_labels[$document->state];
        $monitoring = Monitoring::find($document->monitoring_id);
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        if (!$current_unit) {
            $current_unit = UnitList::find($document->ou_id);
        }
        if ($worker->role === 0 ) {
            $editpermission = true;
        } else {
            //$editpermission = $this->isEditPermission($worker->permission, $document->state);
            $editpermission = TableEditing::isEditPermission($worker->permission, $document->state);
        }
        $editpermission ? $editmode = 'Редактирование' : $editmode = 'Только чтение';
        $period = Period::find($document->period_id);
        $editedtables = Table::editedTables($document->id, $album->id);
        //$noteditablecells = NECellsFetch::where('f', $form->id)->select('t', 'r', 'c')->get();
        $noteditablecells = NECellsFetch::byOuId($current_unit->id, $this->getRealForm($form)->id);
        $renderingtabledata = $this->composeDataForTablesRendering($this->getRealForm($form), $editedtables, $album);
        $laststate = $this->getLastState($worker, $document, $form, $album);
        $formsections = $this->getFormSections($this->getRealForm($form)->id, $album->id, $document->id);
        \App\RecentDocument::create(['worker_id' => $worker->id, 'document_id' => $document->id, 'occured_at' => Carbon::now(), ]);
        return view($this->dashboardView(), compact(
            'current_unit', 'document', 'worker', 'album', 'statelabel', 'editpermission', 'editmode', 'monitoring',
            'form', 'period', 'editedtables', 'noteditablecells', 'forformtable', 'renderingtabledata',
            'laststate', 'formsections'
        ));
    }

    public function dashboardView()
    {
        return property_exists($this, 'dashboardView') ? $this->dashboardView : 'jqxdatainput.formdashboard';
    }

/*    protected function isEditPermission(int $permission, int $document_state)
    {
        switch (true) {
            case (($permission & config('medinfo.permission.permission_edit_report')) && ($document_state == 2 || $document_state == 16)) :
                $edit_permission = true;
                break;
            case (($permission & config('medinfo.permission.permission_edit_prepared_report')) && $document_state == 4) :
                $edit_permission = true;
                break;
            case (($permission & config('medinfo.permission.permission_edit_accepted_report')) && $document_state == 8) :
                $edit_permission = true;
                break;
            case (($permission & config('medinfo.permission.permission_edit_approved_report')) && $document_state == 32) :
                $edit_permission = true;
                break;
            case (($permission & config('medinfo.permission.permission_edit_aggregated_report')) && $document_state == 0) :
                $edit_permission = true;
                break;
            default:
                $edit_permission = false;
        }
        return $edit_permission;
    }*/

    //Описательная информация для построения гридов динамически
    // возвращается json объект в формате для jqxgrid
    protected function composeDataForTablesRendering(Form $form, array $editedtables, Album $album)
    {
        $tables = Table::OfForm($form->id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album->id);
        })->orderBy('table_index')->get();
        $max_index = $tables->last()->table_index;
        $forformtable = [];
        $datafortables = [];
        foreach ($tables as $table) {
            in_array($table->id, $editedtables) ? $edited = 1 : $edited = 0;
            // данные для таблицы-фильтра для навигации по отчетным таблицам в форме
            $forformtable[] = "{ id: " . $table->id . ", code: '" . $table->table_code . "', name: '" . $table->table_name . "', edited: " . $edited . " }";
            $datafortables[$table->id] = TableEditing::fetchDataForTableRenedering($table, $album);
        }
        $datafortables_json = addslashes(json_encode($datafortables));
        $composedata['tablelist'] = $forformtable;
        $composedata['tablecompose'] = $datafortables_json;
        $composedata['max_index'] = $max_index;
        //$composedata['tablecompose'] = $datafortables;
        return $composedata;
    }

    public function getRealForm(Form $form)
    {
        if ($form->relation) {
            return Form::find($form->relation);
        } else {
            return $form;
        }
    }

    public function fetchValues(int $document, int $album, Table $table)
    {
        $rows = Row::OfTable($table->id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->orderBy('row_index')->get();
        $cols = Column::OfTable($table->id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->orderBy('column_index')->get();
        $data = array();
        $i=0;
        foreach ($rows as $r) {
            $row = array();
            $row['id'] = $r->id;
            foreach($cols as $col) {
                switch ($col->content_type) {
                    case Column::HEADER :
                        if ($col->column_index == 1) {
                            $row[$col->id] = $r->row_name;
                        } elseif ($col->column_index == 2) {
                            $row[$col->id] = $r->row_code;
                        }
                        break;
                    case Column::CALCULATED :
                    case Column::DATA :
                        if ($c = Cell::OfDTRC($document, $table->id, $r->id, $col->id)->first()) {
                            if (is_null($c->value)) {
                                $c->delete();
                            } else {
                                $row[$col->id] = number_format($c->value, $col->decimal_count, '.', '');
                            }
                        }
                        break;
                        default:
                            $row[$col->id] = '#ЧИСЛО!';
                }
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
        $permissionByState = false;
        $permissionBySection = false;
        $supervisor = ($worker->role === 3 || $worker->role === 4) ? true : false;
        if ($worker->role === 0 ) {
            $editpermission = true;
        } else {
            //$permissionByState = $this->isEditPermission($worker->permission, $document->state);
            $permissionByState = TableEditing::isEditPermission($worker->permission, $document->state);
            //$permissionBySection = !$this->isTableBlocked($document, $table);
            $permissionBySection = !TableEditing::isTableBlocked($document->id, $table);
            // вариант 1: изменения запрещены только при соответствующем статусе документа
            //$editpermission = $permissionByState && $permissionBySection;
            // вариант 2: изменения запрещены при соответствующем статусе и во все таблицах принятых разделов для всех пользователей
            //$editpermission = $permissionByState && $permissionBySection;
            // вариант 3: изменения запрещены при соответствующем статусе и во все таблицах принятых разделов для исполнителей за исключением сотрудников,
            // принимающих отчеты
            $editpermission = $permissionByState && ( $permissionBySection || $supervisor);
        }
        if ($editpermission) {
            $ou = $document->ou_id;
            $f = $document->form_id;
            $p = $document->period_id;
            $row = $request->row;
            $col = $request->column;
            $new = $request->value;
            //dd($new);
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
                //$r = ($v) ?: 'No Value'; // $r is set to 'My Value' because $v is evaluated to TRUE
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
            if (!$permissionByState) {
                $data['comment'] = "Отсутствуют права для изменения данных в этом документе (по статусу документа)";
            } elseif (!$permissionBySection) {
                $data['comment'] = "Отсутствуют права для изменения данных в этой таблице (раздел документа принят)";
            } else {
                $data['comment'] = "Отсутствуют права для изменения данных в этом документе";
            }

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

    // TODO: Доработать сохранение настроек редактирования отчета (таблица, фильтры, ширина колонок и т.д.)
    protected function getLastState($worker, Document $document, Form $form, $album)
    {
        $realform = null;
        if ($form->relation) {
            $realform = Form::find($form->relation);
            $form_id = $realform->id;
        } else {
            $form_id = $form->id;
        }
        $laststate = array();
        $current_table = Table::OfForm($form_id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album->id);
        })->orderBy('table_index')->first();
        $laststate['currenttable'] = $current_table;
        return $laststate;
    }

    public function getFormSections($form, $album, $document)
    {
        return FormSection::OfForm($form)->whereHas('albums', function ($query) use($album) {
            $query->where('album_id', $album);
        })->with(['section_blocks' => function ($query) use($document) {
            $query->where('document_id', $document);
        }])->with('tables.table')
            ->orderBy('section_name')
            ->get();
    }

/*    public function isTableBlocked(Document $document, int $table)
    {
        $blockedSections = \App\DocumentSectionBlock::OfDocument($document->id)->Blocked()->with('formsection.tables')->get();
        //dd($blockedSections[0]->formsection->tables[0]->table_id);
        if ($blockedSections) {
            $ids = [];
            foreach($blockedSections as $blockedSection) {
                foreach ($blockedSection->formsection->tables as $t) {
                    $ids[] = $t->table_id;
                }
            }
            if (in_array($table, $ids)) {
                return true;
            }
        }
        return false;
    }*/

}

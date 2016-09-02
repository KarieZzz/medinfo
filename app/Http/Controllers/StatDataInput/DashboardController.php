<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use Illuminate\Auth\GenericUser;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Table;
use App\Form;
use App\Document;

class DashboardController extends Controller
{

/*    public function __construct()
    {
        $this->middleware('datainputauth');
    }*/
    //
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

    /**
     * @param int $document
     * @return array
     */
    protected function getEditedTables(int $document)
    {
        $editedtables = \DB::table('statdata')
            ->join('documents', 'documents.id' ,'=', 'statdata.doc_id')
            ->leftJoin('tables', 'tables.id', '=', 'statdata.table_id')
            ->where('documents.id', $document)
            ->where('tables.deleted', 0)
            ->groupBy('statdata.table_id')
            ->pluck('statdata.table_id');
        return $editedtables;
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
            $cols = $table->columns->where('deleted', 0)->sortBy('column_index');
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
        $current_table = $form->tables->where('deleted', 0)->sortBy('table_code')->first();
        $laststate['currenttable'] = $current_table->id;
        return $laststate;
    }
}

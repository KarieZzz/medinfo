<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\ControlledRow;
use App\ControllingRow;
use App\ControlledColumn;
use App\Medinfo\MIControlTranslater;

class MedinfoControlsAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.micontrols', compact('forms'));
    }

    public function fetchControlledRows(int $table, int $scope)
    {
        return ControlledRow::ofTable($table)->ofControlScope($scope)->with('table')->with('row')->get();
    }

    public function fetchControllingRows(int $table, int $relation)
    {
        return ControllingRow::ofTable($table)->ofRelation($relation)->with('table')->with('row')->get();
    }

    public function fetchColumns(int $firstcol, int $countcol)
    {
        return ControlledColumn::where('rec_id','>=' ,$firstcol)->where('rec_id', '<', $firstcol + $countcol)->get();
    }

    public function MIRulesTranslate(int $table)
    {
        $rules = new MIControlTranslater($table);
        //return $rules->InTableRowControl();
        //return $rules->InFormRowControl();
        //return $rules->InReportRowControl();
        //return $rules->ColumnControl();
        //return $rules->inRowControl();
        $all_rules = $rules->translateAll();

        echo '<pre>';
        echo "[\n";
        foreach ($all_rules['intable'] as $rule) {
            echo "['text' => '$rule', 'comment' => 'внутритабличный контроль', 'table_id' => $table  ],\n";
        }
        foreach ($all_rules['inform'] as $rule) {
            echo "['text' => '$rule', 'comment' => 'внутриформенный контроль', 'table_id' => $table  ],\n";
        }
        foreach ($all_rules['inreport'] as $rule) {
            echo "['text' => '$rule', 'comment' => 'межформенный контроль', 'table_id' => $table  ],\n";
        }
        foreach ($all_rules['columns'] as $rule) {
            echo "['text' => '$rule', 'comment' => 'контроль граф', 'table_id' => $table  ],\n";
        }
        foreach ($all_rules['inrow'] as $rule) {
            echo "['text' => '$rule', 'comment' => 'контроль внутри строки', 'table_id' => $table  ],\n";
        }
        echo ']';
        echo '</pre>';
        //return $rules->translateAll();
    }

}

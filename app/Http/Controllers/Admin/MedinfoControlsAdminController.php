<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\Table;
use App\ControlledRow;
use App\ControllingRow;
use App\ControlledColumn;
use App\Medinfo\MIControlTranslater;
use App\Http\Controllers\Admin\CFunctionAdminController;
use App\CFunction;

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

    public function MIRulesTranslate(int $form)
    {

        $tables = Table::OfForm($form)->get();

        foreach ($tables as $table) {
            $rules = new MIControlTranslater($table->id);
            $all_rules = $rules->translateAll();
            //dd($all_rules);
            echo '<pre>';
            echo "// {$table->table_code}  \n";
            foreach ($all_rules['intable'] as $rule) {
                echo "['text' => '$rule', 'comment' => 'внутритабличный контроль строк', 'level' => 1 ,'table_id' => $table->id  ],\n";
            }
            foreach ($all_rules['inform'] as $rule) {
                echo "['text' => '$rule', 'comment' => 'внутриформенный контроль строк', 'level' => 1 , 'table_id' => $table->id  ],\n";
            }
            foreach ($all_rules['inreport'] as $rule) {
                echo "['text' => '$rule', 'comment' => 'межформенный контроль строк', 'level' => 1 , 'table_id' => $table->id  ],\n";
            }
            foreach ($all_rules['columns'] as $rule) {
                echo "['text' => '$rule', 'comment' => 'контроль граф', 'level' => 1 , 'table_id' => $table->id  ],\n";
            }
            foreach ($all_rules['inrow'] as $rule) {
                echo "['text' => '$rule', 'comment' => 'контроль внутри строки', 'level' => 1 , 'table_id' => $table->id  ],\n";
            }
            echo '</pre>';

        }


    }

    public function BatchRuleSave()
    {

        $rules = [

        ];
        $table = Table::find($rules[0]['table_id']);
        $dcheck = new CFunctionAdminController();
        echo '<pre>';
        foreach ($rules as $rule) {
            $cache = $dcheck->compile($rule['text'], $table);
            if (!$cache) {
                echo "<span style='color: red'>Правило: " . $rule['text'] . " не сохранено. " . $dcheck->compile_error . "</span>\n";
            } else {
                $rule['function'] = $dcheck->functionIndex;
                $this->saveScript($rule, $cache);
                echo "<span style='color: green'>Правило: " . $rule['text'] . " сохранено</span>\n";
            }
        }
        echo '</pre>';
    }

    public function saveScript($rule, $cache)
    {
        $newfunction = new CFunction();
        $newfunction->table_id = $rule['table_id'];
        $newfunction->level = $rule['level'];
        $newfunction->function = $rule['function'];
        $newfunction->script = $rule['text'];
        $newfunction->comment = $rule['comment'];
        $newfunction->blocked = 0;
        $newfunction->compiled_cashe = $cache;
        return $newfunction->save();
    }

}

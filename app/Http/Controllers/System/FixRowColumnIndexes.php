<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class FixRowColumnIndexes extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function fixRowIndexes()
    {
        $tables = \App\Table::all();
        $changes = [];
        foreach ($tables as $table) {
            $rows = \App\Row::OfTable($table->id)->orderBy('row_index')->get();
            //dd($rows);
            $rcount = $rows->count();
            for ($i = 0; $i < $rcount; $i++) {
                if ($rows[$i]->row_index !== ($i + 1)) {
                    $changes[] = ['table_id' => $table->id, 'table_code' => $table->table_code ,'table_name' => $table->table_code, 'row_code' => $rows[$i]->row_code];
                }
                $rows[$i]->row_index = $i + 1;
                $rows[$i]->save();
            }
        }
        dd($changes);
    }

    public function fixColumnIndexes()
    {
        $tables = \App\Table::all();
        $changes = [];
        foreach ($tables as $table) {
            $columns = \App\Column::OfTable($table->id)->orderBy('column_index')->get();
            $colcount = $columns->count();
            for ($i = 0; $i < $colcount; $i++) {
                if ($columns[$i]->column_index !== ($i + 1)) {
                    $changes[] = ['table_id' => $table->id, 'table_code' => $table->table_code ,'table_name' => $table->table_code, 'column_code' => $columns[$i]->column_code];
                }
                $columns[$i]->column_index = $i + 1;
                $columns[$i]->save();
            }
        }
        dd($changes);
    }

    public function fixTableIndexes()
    {
        $forms = \App\Form::all();
        $changes = [];
        foreach ($forms as $form) {
            $tables = \App\Table::OfForm($form->id)->orderBy('table_code')->get();
            $tcount = $tables->count();
            for ($i = 0; $i < $tcount; $i++) {
                if ($tables[$i]->table_index !== ($i + 1)) {
                    $changes[] = ['form_id' => $form->id, 'form_code' => $form->form_code , 'table_code' => $tables[$i]->table_code];
                }
                $tables[$i]->table_index = $i + 1;
                $tables[$i]->save();
            }

        }
        dd($changes);
    }
}

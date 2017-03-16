<?php

namespace App\Http\Controllers\Admin;

use App\UnitGroup;
use Illuminate\Http\Request;

//use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\Row;
use App\Table;
use App\Unit;
use App\Album;
use App\Period;
use App\Cell;
use App\Column;
use App\Document;
use Maatwebsite\Excel\Facades\Excel;

class BriefReferenceMaker extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compose_query()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        $upper_levels = Unit::UpperLevels()->orderBy('unit_code')->get(['id', 'unit_name']);
        $groups = UnitGroup::all(['id', 'group_name']);
        $merged = $upper_levels->merge($groups);
        dd($merged->all());

        return view('reports.composequickquery', compact('forms', 'upper_levels'));
    }

    public function fetchActualRows(int $table)
    {
        $default_album = Album::Default()->first()->id;
        return Row::OfTable($table)->with('table')->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album);
        })->get();
    }

    public function fetchDataTypeColumns(int $table)
    {
        $default_album = Album::Default()->first()->id;
        return Column::OfTable($table)->OfDataType()->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album);
        })->get();
    }

    public function makeBriefReport(Request $request) {
        $this->validate($request, [
                'form' => 'required|integer',
                'table' => 'required|integer',
                'rows' => 'required',
                'columns' => 'required',
                'mode' => 'required|in:1,2',
                'level' => 'integer',
                'output' => 'required|in:1,2',
            ]
        );
        //$default_album = Album::Default()->first(['id']);
        $document_type = 1;
        $form = Form::find($request->form);
        $table = Table::find($request->table);
        $mode = $request->mode;
        $rows = explode(',', $request->rows);
        $columns = explode(',', $request->columns);
        $level = $request->level;
        $output = $request->output;
        $group_title = '';
        $el_name = '';

        //dd($columns);
        if ($level == 0) {
            $units = Unit::Primary()->orderBy('unit_code')->get();
            $top = Unit::find(0);
        } else {
            //collect($units = Unit::getPrimaryDescendants($level));
            $units = collect(Unit::getPrimaryDescendants($level));
            $top = $units->shift();
        }
        //dd($units);

        $column_titles = [];
        if ($mode == 1) {
            $group_title = 'По строке: ';
            $gouping_row = Row::find($rows[0]);
            $el_name = $gouping_row->row_code . ' "' . $gouping_row->row_name . '"';
            foreach ($columns as $column) {
                $c = Column::find($column);
                $column_titles[] = $c->column_index . ': ' .$c->column_name;
            }
        } elseif ($mode == 2) {
            $group_title = 'По графе: ';
            $gouping_column = Column::find($columns[0]);
            $el_name = $gouping_column->column_index . ' "' . $gouping_column->column_name . '"';
            foreach ($rows as $row) {
                $r = Row::find($row);
                $column_titles[] = $r->row_code . ': '  . $r->row_name;
            }
        }
        //dd($column_titles);
        $period = Period::orderBy('begin_date', 'desc')->first();
        $values = [];
        $i = 0;
        foreach ($units as $unit) {
            $d = Document::OfTUPF($document_type, $unit->id, $period->id, $form->id)->first();
            if (!is_null($d)) {
                if ($mode == 1) {
                    $i = 0;
                    foreach ($columns as $column) {
                        $cell = Cell::ofDTRC($d->id, $table->id, $rows[0], $column)->first();
                        is_null($cell) ? $value = 0 : $value = $cell->value;
                        $output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        $i++;
                    }
                } elseif ($mode == 2) {
                    $i = 0;
                    foreach ($rows as $row) {
                        $cell = Cell::ofDTRC($d->id, $table->id, $row, $columns[0])->first();
                        is_null($cell) ? $value = 0 : $value = $cell->value;
                        $output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        $i++;
                    }
                }
            } else {
                $i = 0;
                foreach ($column_titles as $c) {
                    $values[$unit->id][$i] = 'N/A';
                    //$values[$unit->id][$i] = 0;
                    $i++;
                }
            }
        }
        //dd($values);
        if ($output == 1) {
            return view('reports.briefreference', compact('form', 'table', 'top','group_title', 'el_name', 'period', 'units', 'column_titles', 'values'));
        } elseif ($output == 2) {
            $excel = Excel::create('Reference');
            $excel->sheet($table->table_code , function($sheet) use ($form, $table, $top, $group_title, $el_name, $period, $units, $column_titles, $values) {
                $sheet->loadView('reports.br_excel', compact('form', 'table', 'top','group_title', 'el_name', 'period', 'units', 'column_titles', 'values'));
            });
            $excel->export('xlsx');
        }
    }

}

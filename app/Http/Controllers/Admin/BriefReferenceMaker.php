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
use App\UnitGroupMember;
use App\UnitsView;
use App\Album;
use App\Period;
use App\Cell;
use App\Column;
use App\Document;
use App\Medinfo\ReportMaker;
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
        $periods = Period::orderBy('name')->get();
        $last_year = Period::LastYear()->first();
        //dd($last_year);
        //$upper_levels = Unit::UpperLevels()->orderBy('unit_code')->get(['id', 'unit_name']);
        $upper_levels = UnitsView::whereIn('type', [1,2,5])->get();
        return view('reports.composequickquery', compact('forms', 'upper_levels', 'periods', 'last_year'));
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
                'period' => 'required|integer',
                'form' => 'required|integer',
                'table' => 'required|integer',
                'rows' => 'required',
                'columns' => 'required',
                'mode' => 'required|in:1,2',
                'level' => 'integer',
                'type' => 'required|in:1,2,5',
                'aggregate' => 'required|in:1,2,3',
                'output' => 'required|in:1,2',
            ]
        );
        //$default_album = Album::Default()->first(['id']);
        $document_type = 1;
        $period = Period::find($request->period);
        $form = Form::find($request->form);
        $table = Table::find($request->table);
        $mode = $request->mode;
        $rows = Row::whereIn('id', explode(',', $request->rows))->get();
        $columns = Column::whereIn('id', explode(',', $request->columns))->get();
        $level = (int)$request->level;
        $type = (int)$request->type;
        $aggregate_level = (int)$request->aggregate;
        $output = $request->output;
        $group_title = '';
        $el_name = '';

        //dd($type);
        //dd($columns);

        if ($level == 0) {
            $units = Unit::Primary()->orderBy('unit_code')->get();
            $top = Unit::find(0);
        } else {
            if ($type == 1 || $type == 2) {
                $units = collect(Unit::getPrimaryDescendants($level));
                //dd($units);
                $top = $units->shift();
            } elseif ($type == 5) {
                $top = UnitGroup::find($level);
                $members = UnitGroupMember::OfGroup($level)->get(['ou_id']);
                $units = Unit::whereIn('id', $members)->get();
                //dd($members);
            }

        }
        //dd($units);

        $column_titles = [];
        if ($mode == 1) {
            $group_title = 'По строке: ';
            //$grouping_row = Row::find($rows[0]);
            $grouping_row = $rows[0];
            $el_name = $grouping_row->row_code . ' "' . $grouping_row->row_name . '"';
            foreach ($columns as $column) {
                //$c = Column::find($column);
                //$column_titles[] = $c->column_index . ': ' .$c->column_name;
                $column_titles[] = $column->column_index . ': ' . $column->column_name;
            }
        } elseif ($mode == 2) {
            $group_title = 'По графе: ';
            //$grouping_column = Column::find($columns[0]);
            $grouping_column = $columns[0];
            $el_name = $grouping_column->column_index . ' "' . $grouping_column->column_name . '"';
            foreach ($rows as $row) {
                //$r = Row::find($row);
                //$column_titles[] = $r->row_code . ': '  . $r->row_name;
                $column_titles[] = $row->row_code . ': '  . $row->row_name;
            }
        }

        if ($aggregate_level == 1) {
            $values = self::getValues($units, $period, $form, $table, $column_titles, $columns, $rows, $mode, $document_type, $output);
        } elseif ($aggregate_level == 2) {
            $units = Unit::legal()->active()->orderBy('unit_code')->get();
            $values = self::getAggregatedValues($units, $period, $form, $table, $column_titles, $columns, $rows, $mode, $output, $aggregate_level);
        } elseif ($aggregate_level == 3) {
            $units = Unit::upperLevels()->active()->orderBy('unit_code')->get();
            $values = self::getAggregatedValues($units, $period, $form, $table, $column_titles, $columns, $rows, $mode, $output, $aggregate_level);
        }

        //dd($column_titles);
        //$period = Period::orderBy('begin_date', 'desc')->first();
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

    public static function getValues($units, Period $period, Form $form, Table $table, $column_titles, $columns, $rows, $mode, $document_type = 1, $output = 1)
    {
        $values = [];
        $values[999999] = []; // Сумма в итоговую строку
        $i = 0;
        foreach ($units as $unit) {
            $d = Document::OfTUPF($document_type, $unit->id, $period->id, $form->id)->first();
            if (!is_null($d)) {
                if ($mode == 1) {
                    $i = 0;
                    foreach ($columns as $column) {
                        //$cell = Cell::ofDTRC($d->id, $table->id, $rows[0], $column)->first();
                        $cell = Cell::ofDTRC($d->id, $table->id, $rows[0]->id, $column->id)->first();
                        is_null($cell) ? $value = 0 : $value = $cell->value;
                        $output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        isset($values[999999][$i]) ? $values[999999][$i] += $value : $values[999999][$i] = $value;
                        $i++;
                    }
                } elseif ($mode == 2) {
                    $i = 0;
                    foreach ($rows as $row) {
                        //$cell = Cell::ofDTRC($d->id, $table->id, $row, $columns[0])->first();
                        $cell = Cell::ofDTRC($d->id, $table->id, $row->id, $columns[0]->id)->first();
                        is_null($cell) ? $value = 0 : $value = $cell->value;
                        $output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        isset($values[999999][$i]) ? $values[999999][$i] += $value : $values[999999][$i] = $value;
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
        return $values;
    }

    public static function getAggregatedValues($units, Period $period, Form $form, Table $table, $column_titles, $columns, $rows, $mode, $output = 1, $aggregate_level = 2)
    {
        $values = [];
        $values[999999] = []; // Сумма в итоговую строку
        //$i = 0;
        //if ($aggregate_level == 2) {
          //  $units = Unit::legal()->active()->orderBy('unit_code')->get();
        //} elseif ($aggregate_level == 3) {
          //  $units = Unit::upperLevels()->active()->orderBy('unit_code')->get();
        //}
        //$rows = Row::whereIn('id', $rows)->get();
        //$columns = Column::whereIn('id', $columns)->get();
        //dd($units);
        foreach ($units as $unit) {
            //if ($unit->aggregate) {
                if ($mode == 1) {
                    $i = 0;
                    foreach ($columns as $column) {
                        $value = ReportMaker::getAggregatedValue($unit, $form, $period, $table->table_code, $rows[0]->row_code, $column->column_index);
                        //var_dump($value);
                        //is_null($cell) ? $value = 0 : $value = $cell->value;
                        $output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        isset($values[999999][$i]) ? $values[999999][$i] += $value : $values[999999][$i] = $value;
                        $i++;
                    }
                } elseif ($mode == 2) {
                    $i = 0;
                    foreach ($rows as $row) {
                        $value = ReportMaker::getAggregatedValue($unit, $form, $period, $table->table_code, $row->row_code, $columns[0]->column_index);
                        //is_null($cell) ? $value = 0 : $value = $cell->value;
                        $output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        isset($values[999999][$i]) ? $values[999999][$i] += $value : $values[999999][$i] = $value;
                        $i++;
                    }
                }
            }
        //}
        //dd($values);
        return $values;
    }
}

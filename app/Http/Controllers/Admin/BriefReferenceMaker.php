<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

//use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Form;
use App\Row;
use App\Table;
use App\Unit;
use App\UnitList;
use App\UnitListMember;
//use App\UnitsView;
use App\Album;
use App\Period;
use App\Cell;
use App\Column;
use App\Document;
use App\Medinfo\ExcelExport;
use App\Medinfo\ReportMaker;
use Maatwebsite\Excel\Facades\Excel;

class BriefReferenceMaker extends Controller
{
    //
    public function fetchActualRows(int $table)
    {
        $default_album = Album::Default()->first()->id;
        return Row::OfTable($table)->with('table')->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album);
        })->orderBy('row_index')->get();
    }

    public function fetchDataTypeColumns(int $table)
    {
        $default_album = Album::Default()->first()->id;
        return Column::OfTable($table)->OfDataType()->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album);
        })->orderBy('column_index')->get();
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
        set_time_limit(240);
        $document_type = 1;
        $period = Period::find($request->period);
        $form = Form::find($request->form);
        $table = Table::find($request->table);
        $mode = $request->mode;
        $rows = Row::whereIn('id', explode(',', $request->rows))->orderBy('row_index')->get();
        $columns = Column::whereIn('id', explode(',', $request->columns))->orderBy('column_index')->get();
        $level = (int)$request->level;
        $type = (int)$request->type;
        //dd($type);
        $aggregate_level = (int)$request->aggregate;
        $output = $request->output;
        $group_title = '';
        $el_name = '';
        if ($level == 0) {
            $units = Unit::Primary()->orderBy('unit_code')->get();
            $top = Unit::find(0);
        } else {
            if ($type == 1 || $type == 2) {
                $units = collect(Unit::getPrimaryDescendants($level));
                //dd($units);
                //$top = $units->shift();
                $top = Unit::find($level);
            } elseif ($type == 100) {
                $top = UnitList::find($level);
                $members = UnitListMember::OfGroup($level)->get(['ou_id']);
                $units = Unit::whereIn('id', $members)->get();
                //dd($members);
            }

        }
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
            //$units = Unit::upperLevels()->active()->orderBy('unit_code')->get();
            $units = Unit::Territory()->active()->orderBy('unit_code')->get();
            // Добавляем аггрегаты группы областных и федеральных учреждений - коды 10002 и 10003
            // TODO: перенести коды в конфиг
            $regional = Unit::where('unit_code', '10002')->first();
            $federal = Unit::where('unit_code', '10003')->first();
            $units->push($regional);
            $units->push($federal);
            //dd($units);
            $values = self::getAggregatedValues($units, $period, $form, $table, $column_titles, $columns, $rows, $mode, $output, $aggregate_level);
        }
        $tablewidth = count($column_titles) + 2;
        $tableheight = count($units) + 7;
        if ($output == 1) {
            return view('reports.briefreference', compact('form', 'table', 'top','group_title', 'el_name', 'period', 'units', 'column_titles', 'values'));
        } elseif ($output == 2) {
            $excel = Excel::create('Reference');
            $excel->sheet("Форма {$form->form_code}, таблица {$table->table_code}" , function($sheet) use ($form, $table, $top, $group_title, $el_name, $period, $units,
                $column_titles, $tablewidth, $tableheight, $values) {
                $sheet->loadView('reports.br_excel', compact('form', 'table', 'top','group_title', 'el_name', 'period', 'units', 'column_titles', 'values'));
                //$highestrow = $sheet->getHighestRow();
                //$sheet->getColumnDimensionByColumn('C5:BZ5')->setAutoSize(false);
                //$sheet->getColumnDimensionByColumn('C5:BZ5')->setWidth('10');
                //$sheet->getColumnDimensionByColumn('B')->setAutoSize(false);
                //$sheet->getColumnDimensionByColumn('B')->setWidth('80');
                $sheet->getRowDimension('6')->setRowHeight(-1);
                //dd(self::getCellByRC(6, 0) . ':' . self::getCellByRC(6, $tablewidth));
                $sheet->getStyle(ExcelExport::getCellByRC(6, 1) . ':' . ExcelExport::getCellByRC(6, $tablewidth))->getAlignment()->setWrapText(true);
                $sheet->getStyle('B7:B430')->getAlignment()->setWrapText(true);
                $sheet->getStyle(ExcelExport::getCellByRC(6, 1) . ':' . ExcelExport::getCellByRC($tableheight, $tablewidth))->getBorders()
                    ->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
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
                        //$output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        $values[$unit->id][$i] = (float)$value;
                        isset($values[999999][$i]) ? $values[999999][$i] += (float)$value : $values[999999][$i] = (float)$value;
                        $i++;
                    }
                } elseif ($mode == 2) {
                    $i = 0;
                    foreach ($rows as $row) {
                        //$cell = Cell::ofDTRC($d->id, $table->id, $row, $columns[0])->first();
                        $cell = Cell::ofDTRC($d->id, $table->id, $row->id, $columns[0]->id)->first();
                        is_null($cell) ? $value = 0 : $value = $cell->value;
                        //$output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        $values[$unit->id][$i] = (float)$value;
                        isset($values[999999][$i]) ? $values[999999][$i] += (float)$value : $values[999999][$i] = (float)$value;
                        $i++;
                    }
                }
                //$output == 1 ? $values[999999][$i] = number_format($values[999999][$i], 2, ',', '') : $values[999999][$i] = (float)$values[999999][$i];
                //dd($values[999999][$i]);
            } else {
                $i = 0;
                foreach ($column_titles as $c) {
                    $values[$unit->id][$i] = 'N/A';
                    //$values[$unit->id][$i] = 0;
                    $i++;
                }
            }
        }
        //var_dump($values[1]);
        return $values;
    }

    public static function getAggregatedValues($units, Period $period, Form $form, Table $table, $column_titles, $columns, $rows, $mode, $output = 1, $aggregate_level = 2)
    {
        $values = [];
        $values[999999] = []; // Сумма в итоговую строку
        $level = 1; // для передачи  в репорт ммейкер
        $sort_order = 3; // для передачи  в репорт ммейкер
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
            $rcontroller = new ReportMaker($level = 1, $period->id, $sort_order = 1);
                if ($mode == 1) {
                    $i = 0;
                    foreach ($columns as $column) {
                        //$value = ReportMaker::getAggregatedValue($unit, $form, $period, $table->table_code, $rows[0]->row_code, $column->column_index);
                        $value = $rcontroller->getAggregatedValue($unit, $form, $table->table_code, $rows[0]->row_code, $column->column_index);
                        //var_dump($value);
                        //is_null($cell) ? $value = 0 : $value = $cell->value;
                        //$output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        $values[$unit->id][$i] = (float)$value;
                        isset($values[999999][$i]) ? $values[999999][$i] += (float)$value : $values[999999][$i] = (float)$value;
                        $i++;
                    }
                } elseif ($mode == 2) {
                    $i = 0;
                    foreach ($rows as $row) {
                        //$value = ReportMaker::getAggregatedValue($unit, $form, $period, $table->table_code, $row->row_code, $columns[0]->column_index);
                        $value = $rcontroller->getAggregatedValue($unit, $form, $table->table_code, $row->row_code, $columns[0]->column_index);
                        //is_null($cell) ? $value = 0 : $value = $cell->value;
                        //$output == 1 ? $values[$unit->id][$i] = number_format($value, 2, ',', '') : $values[$unit->id][$i] = (float)$value;
                        $values[$unit->id][$i] = (float)$value;
                        isset($values[999999][$i]) ? $values[999999][$i] += (float)$value : $values[999999][$i] = (float)$value;
                        $i++;
                    }
                }
            }
        //}
        //dd($values);
        return $values;
    }
}

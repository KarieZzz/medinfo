<?php

namespace App\Http\Controllers\StatDataInput;

use App\Document;
use App\Table;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Medinfo\ExcelExport;
use App\Column;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{
    //
    public function formExport(int $document)
    {
        $excel = new ExcelExport($document);
        //$excel->fillTables();
        $output = $excel->output();
        return response()->download($output['storage_path'], $output['file_name']);
        //return response()->download($output['storage_path'], $output['file_name'], $output['headers']);
        //return response()->download($output['storage_path']);
    }

    public function dataTableExport(int $doc_id, int $table_id)
    {
        $document = Document::find($doc_id);
        $album = $document->album_id;
        $table = Table::find($table_id);
        $rows = \App\Row::OfTable($table->id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->orderBy('row_index')->get();
        $cols = \App\Column::OfTable($table->id)->WhithoutComment()->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->orderBy('column_index')->get();
/*        $column_titles = [];
        foreach($cols as $col) {
            $column_titles[] = $col->column_name;
        }*/
        $data = array();
        $i=0;
        foreach ($rows as $r) {
            $row = array();
            //$row['id'] = $r->id;
            foreach($cols as $col) {
                switch ($col->content_type) {
                    case Column::HEADER :
                        if ($col->column_index == 1) {
                            $row[] = $r->row_name;
                        } elseif ($col->column_index == 2) {
                            $row[] = "$r->row_code;";
                            //$row[] = $r->row_code;
                        }
                        break;
                    case Column::CALCULATED :
                    case Column::DATA :
                        if ($c = \App\Cell::OfDTRC($document->id, $table->id, $r->id, $col->id)->first()) {
                            $row[] = number_format($c->value, $col->decimal_count, '.', '');
                        } else {
                            $row[] = null;
                        }
                        break;
                  }
            }
            $data[$i] = $row;
            $i++;
        }
        //dd($data);
        $excel = Excel::create('Table' . $table->table_code);
        $excel->sheet("Форма {$table->table_code}, таблица {$table->table_code}" , function($sheet) use ($table, $cols, $data) {
            $sheet->loadView('reports.datatable_excel', compact('table', 'cols', 'data'));
            //$sheet->getColumnDimensionByColumn('C5:BZ5')->setAutoSize(false);
            //$sheet->getColumnDimensionByColumn('C5:BZ5')->setWidth('10');
            //$sheet->getColumnDimensionByColumn('B')->setAutoSize(false);
            //$sheet->getColumnDimensionByColumn('B')->setWidth('80');
            //$sheet->getRowDimension('6')->setRowHeight(-1);
            $sheet->getStyle(ExcelExport::getCellByRC(4, 1) . ':' . ExcelExport::getCellByRC(4, count($cols)))->getAlignment()->setWrapText(true);
            //$sheet->getStyle('B7:B430')->getAlignment()->setWrapText(true);
            $sheet->getStyle(ExcelExport::getCellByRC(4, 1) . ':' . ExcelExport::getCellByRC(count($data)+5, count($cols)))->getBorders()
                ->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            //$sheet->getStyle(ExcelExport::getCellByRC(3, 2) . ':' . ExcelExport::getCellByRC(count($data)+3, 2))->setQuotePrefix(true);
            $sheet->getStyle(ExcelExport::getCellByRC(4, 2) . ':' . ExcelExport::getCellByRC(count($data)+5, 2))->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

/*            $sheet->setColumnFormat(array(
                'A1:A35' => '@',
                'B1:B35' => '@',
            ));*/

        });
        $excel->export('xlsx');
    }

}
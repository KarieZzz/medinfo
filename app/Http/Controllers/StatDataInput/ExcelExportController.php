<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Document;
use App\Unit;
use App\Period;
use App\Form;
use App\Table;
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

    public function dataTableExport(Document $document, Table $table)
    {
        $ret = ExcelExport::getTableDataForExport($document, $table);
        $data = $ret['data'];
        $cols = $ret['cols'];
        //dd($data);
        $excel = Excel::create('Table' . $table->table_code);
        $excel->sheet("Таблица {$table->table_code}" , function($sheet) use ($table, $cols, $data) {
            $sheet->loadView('reports.datatable_excel', compact('table', 'cols', 'data'));
/*            $sheet->setColumnFormat(array(
                'A:B' => '@',
            ));*/
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
        });
        $excel->export('xlsx');
    }

    public function dataFormExport(Document $document)
    {
        $form = Form::find($document->form_id);
        $ou = Unit::find($document->ou_id);
        $period = Period::find($document->period_id);
        $album = $document->album_id;
        $tables = Table::OfForm($form->id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->orderBy('table_index')->get();
        $excel = Excel::create('Form' . $form->form_code);
        $excel->sheet('Титул', function($sheet) use ($form, $ou, $period) {
            $sheet->cell('A1', function($cell) use ($ou){
                $cell->setValue('Учреждение: ' . $ou->unit_name);
                $cell->setFontSize(16);
            });
            $sheet->cell('A2', function($cell) use ($form){
                $cell->setValue('Форма: (' . $form->form_code . ') ' . $form->form_name);
                $cell->setFontSize(16);
            });
            $sheet->cell('A3', function($cell) use ($period){
                $cell->setValue('Период "' . $period->name . '"');
                $cell->setFontSize(16);
            });
            $sheet->cell('A4', function($cell) {
                $cell->setValue('Не для предоставления в МИАЦ в качестве отчетной формы!');
                $cell->setFontColor('#f00000');
                $cell->setFontSize(10);
            });
        });
        foreach ($tables as $table) {
            $ret = ExcelExport::getTableDataForExport($document, $table);
            $data = $ret['data'];
            $cols = $ret['cols'];
            $excel->sheet($table->table_code , function($sheet) use ($table, $cols, $data) {
                $sheet->loadView('reports.datatable_excel', compact('table', 'cols', 'data'));
                $sheet->getStyle(ExcelExport::getCellByRC(4, 1) . ':' . ExcelExport::getCellByRC(4, count($cols)))->getAlignment()->setWrapText(true);
                $sheet->getStyle(ExcelExport::getCellByRC(4, 1) . ':' . ExcelExport::getCellByRC(count($data)+5, count($cols)))->getBorders()
                    ->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
                $sheet->getStyle(ExcelExport::getCellByRC(4, 2) . ':' . ExcelExport::getCellByRC(count($data)+5, 2))->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            });
        }
        $excel->setActiveSheetIndex(0);
        $excel->export('xlsx');
    }

}
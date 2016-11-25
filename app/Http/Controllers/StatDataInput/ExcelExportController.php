<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Medinfo\ExcelExport;

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

    public function dataTableExport(int $document, int $table)
    {
        //$directories = Storage::directories('templates');
        //$time = Storage::lastModified('templates/excel/s30.xlsx');
        //dd($time);
        //dd($directories);
        //Storage::disk('local')->put('file.txt', 'Contents');
        //$ex_file = Excel::create('Table', function($excel) use($document) {

        //$path = storage_path('app/templates/excel/s30.xlsx');
        //$objReader = PHPExcel_IOFactory::createReader('Excel2007');
        //$_phpexcel = $objReader->load($path);
        $excel = new ExcelExport($document);
        //$excel->fillTables();
        $excel->output();
        //$ex_file = Excel::load($path);
        //$ex_file = Excel::load($path, function($reader) use($document) {
/*            $excel->setTitle('Данные таблицы ' . ' документа №' . $document)
                ->setCreator('А.Ю. Шамеев')
                ->setCompany('МИАЦ ИО');

            // Call them separately
            $excel->setDescription('Выгрузка данных выделенной таблицы без форматирования по шаблону формы ФСН');
            $excel->sheet('Таблицы', function($sheet) {
                $all_table = Table::all();
                $sheet->fromModel($all_table);
                $sheet->freezeFirstRow();

            });*/
        //});
        //$ex_file->download('xlsx');
        //$this->output_to_web($_phpexcel);
    }

}
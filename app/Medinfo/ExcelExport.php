<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 25.08.2016
 * Time: 16:13
 */

namespace App\Medinfo;

use App\Table;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use App\Medinfo\CeLLIterator;
use App\Period;
use App\Document;
use App\Unit;
use App\Form;
use App\Cell;

class ExcelExport
{
    private $doc_id;
    private $_unit;
    private $_form;
    private $_period;
    private $_phpexcel; // объект PHPexcel
    private $_ranges; // именованные диапазоны excel

    public function __construct($doc_id)
    {
        if (!$doc_id) {
            throw new Exception("Не определен Id документа для экспорта в эксель");
        }
        set_time_limit(240);
        $this->doc_id = $doc_id;
        $document = Document::find($doc_id);
        $this->_unit = Unit::find($document->ou_id);
        $this->_form = Form::find($document->form_id);
        $this->_period = Period::find($document->period_id); // Год в формате YYYY
        $template_url = $this->setTemplateUrl($this->_form->form_code);
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $this->_phpexcel = $objReader->load($template_url);
        $this->_ranges   = $this->_phpexcel->getNamedRanges();
        $this->fillSubject($this->_unit->unit_name);
        $this->fillPeriod($this->_period->name);
    }

    private function setTemplateUrl($code)
    {
        $path = storage_path('app/templates/excel/s' . $code .'.xlsx');
        if (!is_file($path)) {
            throw new Exception('Файл шаблона отчета не существует');
        }
        return $path;
    }

    public function fillSubject($subject)
    {
        $subject_range = $this->_phpexcel->getNamedRange('subject');
        $aSheet = $subject_range->getWorksheet();
        $aSheet->setCellValue($subject_range->getRange(), $subject);
    }

    public function fillPeriod($period)
    {
        $period_range = $this->_phpexcel->getNamedRange('period');
        $aSheet = $period_range->getWorksheet();
        $aSheet->setCellValue($period_range->getRange(), $period);
    }

    public function fillTables()
    {
        $tables = Table::where('form_id', $this->_form->id)->where('deleted', 0)->get();
        foreach ($tables as $table ) {
            //dd($table);
            $cell_iterator = new CellIterator($table);
            //$cell_iterator->setDocumentId($this->doc_id);
            $cellArrayToWrite = $this->get_table_cells($table->table_code);
            //dd($cell_iterator);
            $this->fill_table($cellArrayToWrite, $cell_iterator);
        }
    }

/*    public function fillTable($table_id)
    {
        $table = new Table($table_id);
        $cell_iterator = new CellIterator($table_id);
        $cell_iterator->setDocumentId($this->doc_id);
        $cell_iterator->setDataOnlyCells(true);
        $cell_iterator->setCollection();
        $cellArrayToWrite = $this->get_table_cells($table->getCode());
        $this->fill_table($cellArrayToWrite, $cell_iterator);
    }*/

    private function fill_table($cellArrayToWrite, $cell_iterator)
    {
        $cell_iterator->first();
        foreach ($cellArrayToWrite as $excel_cell) {
            $c['excel'] = $excel_cell;
            $c['mi'] = $cell_iterator->current();
            $this->set_value($c);
            $cell_iterator->next();
        }
    }

    private function set_value($filling)
    {
        $c = $filling['mi'];
        $value = null;
        if ($cell = Cell::ofDTRC($this->doc_id, $c['t'], $c['r'], $c['c'])->first()) {
            $value = $cell->value;
        }
        $filling['excel']->setValue($value);
        return $value;
    }

    private function get_table_cells($table_code)
    {
        $cellArrayToWrite = array();
        foreach($this->_ranges as $name => $namedRange) {
            $pattern =  '/' . $table_code . '(?![a-z]+)/ux';

            if (preg_match($pattern, $name)) {
                //echo $pattern . " " . $name . PHP_EOL;
                $coordinates = $namedRange->getRange();
                $range_corners = $this->get_range_corners($coordinates);
                $h = $range_corners[2] - $range_corners[0];
                $w = $range_corners[3] - $range_corners[1];
                $aSheet = $namedRange->getWorksheet();
                for($i = 0; $i <= $h; $i++) {
                    for ($j = 0; $j <= $w; $j++) {
                        $cell = $aSheet->getCellByColumnAndRow($range_corners[1] + $j, $range_corners[0] + $i);
                        $v = $cell->getValue();
                        if ($v == '&&' || $v == 'X') {
                            $cellArrayToWrite[] = $cell;
                        }
                    }
                }
            }
        }
        return $cellArrayToWrite;
    }

    private function get_range_corners($range_coordinates)
    {
        $corners = explode(':', $range_coordinates);
        $left_upper = $corners[0];
        preg_match('/[A-Z]+/', $left_upper, $matches);
        $left_upper_colIndex = PHPExcel_Cell::columnIndexFromString($matches[0])-1;
        $left_upper_rowIndex = (int)ltrim($left_upper, $matches[0]) ;
        $right_bottom_colIndex = $left_upper_colIndex;
        $right_bottom_rowIndex = $left_upper_rowIndex;
        if (isset($corners[1])) {
            preg_match('/[A-Z]+/', $corners[1], $matches);
            $right_bottom_colIndex = PHPExcel_Cell::columnIndexFromString($matches[0])-1;
            $right_bottom_rowIndex = (int)ltrim($corners[1], $matches[0]) ;
        }
        return $range_corners = array( $left_upper_rowIndex, $left_upper_colIndex, $right_bottom_rowIndex, $right_bottom_colIndex);
    }

    public function save_to_file($file_name = null)
    {
        $path_to_export = storage_path('app/exports/');
        $objWriter = PHPExcel_IOFactory::createWriter($this->_phpexcel, 'Excel2007');
        if ($file_name) {
            $objWriter->save($path_to_export . $file_name . '.xlsx');
        }
        else {
            $objWriter->save($this->_unit->unit_code . '_' . $this->_form->form_code . '.xlsx');
        }
        return true;
    }

    public function output_to_web()
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->_phpexcel, 'Excel2007');
        ob_end_clean();
        //ini_set('zlib.output_compression','Off');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->_unit->unit_code . '_' . $this->_form->form_code . '.xlsx' . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

}
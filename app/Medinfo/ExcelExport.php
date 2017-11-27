<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 25.08.2016
 * Time: 16:13
 */

namespace App\Medinfo;


use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Storage;
use Carbon\Carbon;
use Response;

use App\Medinfo\CeLLIterator;
use App\Period;
use App\Document;
use App\Unit;
use App\Form;
use App\Table;
use App\UnitGroup;
use App\Cell;

class ExcelExport
{
    private $doc_id;
    private $_unit;
    private $_form;
    private $_period;
    private $previous_export_actual;
    private $template_cached;
    private $excel_cached;
    private $force_reload_template = false;
    private $force_reload_exports = false;
    private $_phpexcel; // объект PHPexcel
    private $_ranges; // именованные диапазоны excel
    private $unitgroupName;
    private $unitgroupCode;

    public function __construct($doc_id)
    {
        if (!$doc_id) {
            throw new Exception("Не определен Id документа для экспорта в эксель");
        }
        set_time_limit(240);
        $this->doc_id = $doc_id;
        $document = Document::find($doc_id);
        $this->_unit = Unit::find($document->ou_id);
        if (!$this->_unit) {
            $this->_unit = UnitGroup::find($document->ou_id);
            $this->unitgroupCode = $this->_unit->group_code;
            $this->unitgroupName = $this->_unit->group_name;

        } else {
            $this->unitgroupCode = $this->_unit->unit_code;
            $this->unitgroupName = $this->_unit->unit_name;
        }
        $this->_form = Form::find($document->form_id);
        $this->_period = Period::find($document->period_id); // Год в формате YYYY
        $this->previous_export_actual = $this->exportCasheActual();
        //dd($this->previous_export_actual);
    }

    public function setForceReload($state = false)
    {
        $this->force_reload_template = $state;
    }

    private function openTemplate()
    {
        // Проверяем есть ли кэшированная версия объекта эксель
        //$cache_path = storage_path('app/templates/cache/' . $this->_form->id);
        $cache_exists = Storage::disk('template_cache')->exists($this->_form->id);
        if ($cache_exists ) {
            $excel_object = unserialize(Storage::disk('template_cache')->get($this->_form->id));
            $this->template_cached = true;
        } else {
            $template_url = $this->setTemplateUrl($this->_form->form_code);
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $excel_object = $objReader->load($template_url);
            Storage::disk('template_cache')->put($this->_form->id, serialize($excel_object));
            $this->template_cached = false;
            $this->excel_cached = false;
        }
        return $excel_object;
    }

    private function exportCasheActual()
    {
        $updated_at =  Document::dataUpdatedAt($this->doc_id);
        $cahed_at = $this->exportCashedAt();
        return $cahed_at->gt($updated_at);
    }

    private function exportCashedAt()
    {
        $file = $this->doc_id . '.xlsx';
        if ($export_exists = Storage::disk('excel_exports')->exists($file)) {
            $unixtime_modified = Storage::disk('excel_exports')->lastModified($file);
            // "Состарим" файл на одну минуту с учетом возможной продолжительности рендеринга эксельного файла
            return Carbon::createFromTimestamp($unixtime_modified - 30);
        } else {
            // Если не существует, возвращаем объект с заведомо старой датой
            return Carbon::create(1900, 1, 1);
        }
    }

    private function setTemplateUrl($code)
    {
        $path = storage_path('app/templates/excel/s' . $code .'.xlsx');
        if (!is_file($path)) {
            throw new \Exception('Файл шаблона отчета не существует');
        }
        return $path;
    }

    public function getExcelStoragePath()
    {
        return storage_path('app/exports/excel/' . $this->doc_id .'.xlsx');
        //return storage_path('app/exports/excel/' . $this->doc_id .'.pdf');
    }

    private function fillSubject($subject)
    {
        $subject_range = $this->_phpexcel->getNamedRange('subject');
        $aSheet = $subject_range->getWorksheet();
        $aSheet->setCellValue($subject_range->getRange(), $subject);
    }

    private function fillPeriod($period)
    {
        $period_range = $this->_phpexcel->getNamedRange('period');
        $aSheet = $period_range->getWorksheet();
        $aSheet->setCellValue($period_range->getRange(), $period);
    }

    private function fillTables()
    {
        $tables = Table::where('form_id', $this->_form->id)->where('deleted', 0)->get();
        foreach ($tables as $table ) {
            $cell_iterator = new CellIterator($table);
            $cellArrayToWrite = $this->get_table_cells($table->table_code);
            //dd($cell_iterator);
            //dd($cellArrayToWrite);
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
        try {
            $cell_iterator->first();
        } catch (\Exception $e) {
            dd($cell_iterator);
        }

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
        $left_upper_rowIndex = ltrim($left_upper, $matches[0]) ;
        $right_bottom_colIndex = $left_upper_colIndex;
        $right_bottom_rowIndex = $left_upper_rowIndex;
        if (isset($corners[1])) {
            preg_match('/[A-Z]+/', $corners[1], $matches);
            $right_bottom_colIndex = PHPExcel_Cell::columnIndexFromString($matches[0])-1;
            $right_bottom_rowIndex = ltrim($corners[1], $matches[0]) ;
        }
        return $range_corners = array( $left_upper_rowIndex, $left_upper_colIndex, $right_bottom_rowIndex, $right_bottom_colIndex);
    }

    public function saveFile($file_name = null)
    {
        $path_to_export = storage_path('app/exports/excel');
        dd($path_to_export);
        $objWriter = PHPExcel_IOFactory::createWriter($this->_phpexcel, 'Excel2007');
        if ($file_name) {
            $objWriter->save($path_to_export . $file_name . '.xlsx');
        }
        else {
            $objWriter->save($this->_unit->unit_code . '_' . $this->_form->form_code . '.xlsx');
        }
        return true;
    }

    public function output()
    {
        $excel_cashed = $this->previous_export_actual ? '_ec' : '';
        $output = [];
        $output['storage_path'] = $this->getExcelStoragePath();
        // Если предыдущий экспорт не актуален или в случае принудительного обновления файла экспорта
        if (!$this->previous_export_actual || $this->force_reload_exports) {
            $this->_phpexcel = $this->openTemplate();
            $this->_ranges   = $this->_phpexcel->getNamedRanges();
            $this->fillSubject($this->unitgroupName);
            $this->fillPeriod($this->_period->name);
            $this->fillTables();
            $objWriter = PHPExcel_IOFactory::createWriter($this->_phpexcel, 'Excel2007');
            //$objWriter->setOffice2003Compatibility(true);
            //$objWriter = PHPExcel_IOFactory::createWriter($this->_phpexcel, 'PDF');
            $objWriter->save($output['storage_path']);
        }
        //$excel_file = Storage::disk('excel_exports')->get($this->doc_id . '.xlsx');
        $template_cached = $this->template_cached ? '_tc' : '';

        $output['file_name'] =  $this->unitgroupCode . '_' . $this->_form->form_code . $template_cached . $excel_cashed .'.xlsx';
        $output['headers'] = [
            'Content-Typ' => 'application/vnd.ms-excel',
            //'Content-Disposition' => 'attachment;filename="' . $file_name . '"',
            'Cache-Control' => 'max-age=0',
        ];
        return $output;
    }

    public static function getTableDataForExport(Document $document, Table $table)
    {
        $album = $document->album_id;
        $rows = \App\Row::OfTable($table->id)->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->orderBy('row_index')->get();
        $cols = \App\Column::OfTable($table->id)->WhithoutComment()->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album);
        })->orderBy('column_index')->get();
        $data = array();
        $i=0;
        foreach ($rows as $r) {
            $row = array();
            //$row['id'] = $r->id;
            foreach($cols as $col) {
                switch ($col->content_type) {
                    case \App\Column::HEADER :
                        if ($col->column_index == 1) {
                            $row[] = $r->row_name;
                        } elseif ($col->column_index == 2) {
                            $row[] = "$r->row_code;";
                            //$row[] = $r->row_code;
                        }
                        break;
                    case \App\Column::CALCULATED :
                    case \App\Column::DATA :
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
        return ['data' => $data, 'cols' => $cols];
    }


    public static function getCellByRC(int $row, int $column)
    {
        $alphabetic = array('n/a', 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
            'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
            'CA','CB','CC','CD','CE','CF','CG','CH','CI','CJ','CK','CL','CM','CN','CO','CP','CQ','CR','CS','CT','CU','CV','CW','CX','CY','CZ',
            'DA','DB','DC','DD','DE','DF','DG','DH','DI','DJ','DK','DL','DM','DN','DO','DP','DQ','DR','DS','DT','DU','DV','DW','DX','DY','DZ',
            'EA','EB','EC','ED','EE','EF','EG','EH','EI','EJ','EK','EL','EM','EN','EO','EP','EQ','ER','ES','ET','EU','EV','EW','EX','EY','EZ',
            'FA','FB','FC','FD','FE','FF','FG','FH','FI','FJ','FK','FL','FM','FN','FO','FP','FQ','FR','FS','FT','FU','FV','FW','FX','FY','FZ',
            'GA','GB','GC','GD','GE','GF','GG','GH','GI','GJ','GK','GL','GM','GN','GO','GP','GQ','GR','GS','GT','GU','GV','GW','GX','GY','GZ',
            'HA','HB','HC','HD','HE','HF','HG','HH','HI','HJ','HK','HL','HM','HN','HO','HP','HQ','HR','HS','HT','HU','HV','HW','HX','HY','HZ',
            'IA','IB','IC','ID','IE','IF','IG','IH','II','IJ','IK','IL','IM','IN','IO','IP','IQ','IR','IS','IT','IU','IV','IW','IX','IY','IZ',
            );
        return $alphabetic[$column] . $row;

    }

}
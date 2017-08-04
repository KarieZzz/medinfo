<?php

namespace App\Http\Controllers\StatDataInput;

use App\Period;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\DomCrawler\Crawler;
use App\Column;
use App\Document;
use App\Unit;
use App\Form;
use App\Row;
use App\Table;
use App\Cell;

class WordExportController extends Controller
{
    //
    private $dom;
    private $xpath;
    private $path;
    private $zip;
    private $document;
    private $unit;
    private $form;
    private $period;
    private $year_node;
    private $unit_node;
    private $cell_nodes = [];
    const EXPORT_PATH = 'app/exports/word/';

    public function formExport(Document $document)
    {
        $this->document = $document;
        $this->unit = Unit::find($document->ou_id);
        $this->form = Form::find($document->form_id);
        $this->period = Period::find($document->period_id);
        $this->openTemplate();
        $this->modifyDOM();
        $this->saveWordDocument();
        return response()->download($this->path);
    }

    public function openTemplate() {
        $template_path = storage_path('app/templates/word/' . $this->form->form_code .'.docx');
        if (!is_file($template_path)) {
            throw new \Exception('Файл шаблона отчета не существует');
        }
        $export_path = storage_path(WordExportController::EXPORT_PATH . $this->document->id . '.docx');
        if (copy($template_path, $export_path)) {
        //if (copy($template_path, $export_path)) {
            $this->path = $export_path;
        } else {
            throw new \Exception('Не удалось скопировать файл шаблона в папку экспорта');
        }

        $this->zip = new \ZipArchive();
        if ($this->zip->open($this->path) === TRUE) {
            $document_xml =  $this->zip->getFromName('word/document.xml');
            //$zip->close();
        } else {
            throw  new \Exception("Не удалось открыть файл document.xml из архива $this->path");
        }
        $this->dom = new \DOMDocument();
        $this->dom->loadXML($document_xml);
        return true;
    }

    public function saveWordDocument()
    {
        if ($this->zip->addFromString('word/document.xml', $this->dom->saveXML())) {
            $this->zip->close();
        } else {
            throw new \Exception('Ошибка сохранения файла в формате MS Word');
        }
        return;
    }

    public function modifyDOM()
    {
        $nodes = $this->getElements('w:body/w:tbl/w:tr/w:tc/w:p/w:bookmarkStart');
        //$nodes = $this->dom->getElementsByTagName('bookmarkStart');
            foreach ($nodes as $node) {
                $node_name = $node->getAttribute('w:name');
                switch ($node_name) {
                    case 'z0001_000_00' :
                    case 'Z0001_000_00' :
                        $this->year_node = $node;
                        $this->writeValue($this->year_node, $this->period->name);
                        break;
                    case 'z0002_000_00' :
                    case 'Z0002_000_00' :
                        $this->unit_node = $node;
                        $this->writeValue($this->unit_node, $this->unit->unit_name);
                        break;
                    case $node_name[0] === 'z' :
                    case $node_name[0] === 'Z' :
                        $cell_arguments = explode('_', $node->getAttribute('w:name'));
                        $table_code = substr($cell_arguments[0], 1);
                        $row_index = (int)ltrim($cell_arguments[1], '0');
                        $row_medstatcode = $cell_arguments[1];
                        $column_medstatcode = $cell_arguments[2];
                        $column_index = (int)ltrim($cell_arguments[2], '0');
                        try {
                            $table = Table::OfFormTableCode($this->document->form_id, $table_code)->first();
                            if (!$table->transposed ) {
                                try {
                                    $row = Row::OfTableRowMedstatcode($table->id, $row_medstatcode)->first();
                                    $column = Column::OfTableColumnIndex($table->id, $column_index)->first();
                                    if ($cell = Cell::OfDTRC($this->document->id, $table->id, $row->id, $column->id)->first()) {
                                        $value = number_format($cell->value, $column->decimal_count, ',', '');
                                        $this->writeValue($node, $value, $table_code, $row_medstatcode, $column_index);
                                    } else {
                                        $value = 'null';
                                    }
                                    //echo "Закладка на ячейку $node_name, указанная в шаблоне MS Word обработана. Записано значение $value  <br />";
                                } catch (\Exception $e) {
                                    //echo "<p style='color:red '> Закладка на ячейку $node_name, указанная в шаблоне MS Word не верна</p>";
                                }
                            }
                            try {
                                if ($table->transposed == 1) {
                                    $row = Row::OfTableRowMedstatcode($table->id, str_pad($column_index, '3', '0', STR_PAD_LEFT ))->first();
                                    $column = Column::OfTableColumnIndex($table->id, 3)->first();
                                    if ($cell = Cell::OfDTRC($this->document->id, $table->id, $row->id, $column->id)->first()) {
                                        $value = number_format($cell->value, $column->decimal_count, ',', '');
                                        $this->writeValue($node, $value, $table_code, $row_medstatcode, $column_index);
                                    } else {
                                        $value = 'null';
                                    }
                                    //echo "Закладка на ячейку $node_name, указанная в шаблоне MS Word обработана. Записано значение $value  <br />";
                                }
                            } catch (\Exception $e) {
                                //echo "<p style='color:red '> Таблица транспонирована. Закладка на ячейку $node_name, указанная в шаблоне MS Word не верна</p>";
                            }

                        } catch (\Exception $e) {
                            //echo "<p style='color:red '> Закладка на таблицу $node_name, указанная в шаблоне MS Word не верна</p>";

                        }
                        break;
                }
            }
    }

    public function getElements($path, \DOMElement $contextNode = null)
    {
        if ($this->dom === null) {
            return array();
        }
        if ($this->xpath === null) {
            $this->xpath = new \DOMXpath($this->dom);
        }

        if (is_null($contextNode)) {
            return $this->xpath->query($path);
        } else {
            return $this->xpath->query($path, $contextNode);
        }
    }

    public function writeValue($node, $value, $table_code = null, $row_index = null, $column_index = null)
    {
        $p = $node->parentNode;
        // Создаем узлы
        $new_r_node = $this->dom->createElement('w:r');
        $new_t_node = $this->dom->createElement('w:t', $value);
        $new_rPr_element = $this->dom->createElement('w:rPr');
        $new_b_element = $this->dom->createElement('w:b');
        $new_bCs_element = $this->dom->createElement('w:bCs');
        $new_sz_element = $this->dom->createElement('w:sz');
        $new_sz_element->setAttribute("w:val", "18");
        // Добавляем узлы
        $new_r_node->appendChild($new_rPr_element);
        $new_rPr_element->appendChild($new_b_element);
        $new_rPr_element->appendChild($new_bCs_element);
        $new_rPr_element->appendChild($new_sz_element);
        $new_r_node->appendChild($new_t_node);
        $p->appendChild($new_r_node);
    }
}

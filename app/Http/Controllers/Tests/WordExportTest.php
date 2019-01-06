<?php

namespace App\Http\Controllers\Tests;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class WordExportTest extends Controller
{
    //
    public function wordexport()
    {
        $document = \App\Document::find(20358); // 12 форма все за 2018 год
        $unit = \App\UnitsView::find($document->ou_id);
        $form = \App\Form::getRealForm($document->form_id);
        $period = \App\Period::find($document->period_id);
        $template_path = storage_path('app/templates/word/' . rtrim($form->short_ms_code) .'.docx');
        $export_path = storage_path('app/exports/word/' . $document->id . '.docx');
        if (copy($template_path, $export_path)) {
            $path = $export_path;
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) === TRUE) {
            $document_xml =  $zip->getFromName('word/document.xml');
            //$zip->close();
        }
        $dom = new \DOMDocument();
        $dom->loadXML($document_xml);



        if ($zip->addFromString('word/document.xml', $dom->saveXML())) {
            $zip->close();
        }

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
                    $year_node = $node;
                    $this->writeValue($year_node, $this->period->name);
                    break;
                case 'z0002_000_00' :
                case 'Z0002_000_00' :
                    $this->unit_node = $node;
                    $this->writeValue($this->unit_node, $this->unit->name);
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
                        $table = \App\Table::OfFormTableCode($this->form->id, $table_code)->first();
                        if (!$table->transposed ) {
                            $row = \App\Row::OfTableRowMedstatcode($table->id, $row_medstatcode)->first();
                            $column = \App\Column::OfTableMedstatCode($table->id, $cell_arguments[2])->first();
                            if ($cell = \App\Cell::OfDTRC($this->document->id, $table->id, $row->id, $column->id)->first()) {
                                $value = number_format($cell->value, $column->decimal_count, ',', '');
                                //$this->writeValue($node, $value, $table_code, $row_medstatcode, $column_index);
                                $this->writeValue($node, $value, $table_code, $row_medstatcode, $cell_arguments[2]);
                            } else {
                                $value = 'null';
                            }
                        }
                        if ($table->transposed == 1) {
                            $row = \App\Row::OfTableRowMedstatcode($table->id, str_pad($column_index, '3', '0', STR_PAD_LEFT ))->first();
                            $column = \App\Column::OfTableColumnIndex($table->id, 3)->first();
                            if ($cell = \App\Cell::OfDTRC($this->document->id, $table->id, $row->id, $column->id)->first()) {
                                $value = number_format($cell->value, $column->decimal_count, ',', '');
                                $this->writeValue($node, $value, $table_code, $row_medstatcode, $column_index);
                            } else {
                                $value = 'null';
                            }
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

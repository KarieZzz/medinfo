<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\DomCrawler\Crawler;


class WordExportController extends Controller
{

    //
    private $dom = null;
    private $xpath = null;
    private $document = null;
    private $year_node = null;
    private $unit_node = null;
    private $cell_nodes = [];

    public function formExport(int $document)
    {
        //$path = storage_path('app/templates/excel/s' . $code .'.xlsx');
        $path = storage_path('app/templates/word/13/word/document.xml');
        if (!is_file($path)) {
            throw new \Exception('Файл шаблона отчета не существует');
        }
        $this->getDomFromPath($path);
        $this->modifyDOM();
        echo 'Wrote: ' .  $this->dom->save($path) . ' bytes';

        //$this->dom->save($path);
    }

    public function modifyDOM()
    {
        $nodes = $this->getElements('w:body/w:tbl/w:tr/w:tc/w:p/w:bookmarkStart');
        //dd($nodes);
        //dd($nodes->item(0)->getAttribute('w:name'));
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $node_name = $node->getAttribute('w:name');
                switch ($node_name) {
                    case 'z0001_000_00' :
                        $this->year_node = $node;
                        break;
                    case 'z0002_000_00' :
                        $this->unit_node = $node;
                        break;
                    case $node_name[0] === 'z':
                        $cell_arguments = explode('_', $node->getAttribute('w:name'));
                        $table_code = substr($cell_arguments[0], 1);
                        $row_index = ltrim($cell_arguments[1], '0');
                        $column_index = ltrim($cell_arguments[2], '0');
                        $this->writeValue($table_code, $row_index, $column_index, $node);
                        break;
                }
            }
        }
    }

    public function getDomFromPath($path)
    {
        $this->dom = new \DOMDocument();
        $this->dom->load($path);

        return $this->dom;
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

    public function writeValue($table_code, $row_index, $column_index, $node)
    {
        $p = $node->parentNode;
        $wr_nodes = $p->getElementsByTagName('w:r');
        // Если секция w:r уже присутствует в документе, новую не добавляем
        var_dump($p->getElementsByTagName('w:bookmarkStart'));
        //var_dump($wr_nodes);
/*        if ($wr_nodes->length === 0) {
            // Создаем узлы
            $new_r_node = $this->dom->createElement('w:r');
            $new_t_node = $this->dom->createElement('w:t', '666666');
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
            //var_dump($p->childNodes[3]->childNodes[1]);
        }*/



    }
}

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

    public function formExport(int $document)
    {
        //$phpWord = new \PhpOffice\PhpWord\PhpWord();
        //$path = storage_path('app/templates/excel/s' . $code .'.xlsx');
        $path = storage_path('app/templates/word/30/word/document.xml');
        if (!is_file($path)) {
            throw new \Exception('Файл шаблона отчета не существует');
        }
        //$phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
        //var_dump($phpWord->getSections());
        //$crawler = new Crawler();
        //$crawler->addXmlContent($path);
        //dd($crawler);
/*        foreach ($crawler as $domElement) {
            var_dump($domElement->nodeName);
        }*/
        //$handle = file($path);
        //dd($handle);
        //$this->getDomFromString($path);
        //dd($this->dom);
        $this->getDomFromPath($path);
        $this->read();
        //dd($this->dom);
    }

    public function read()
    {
        //$nodes = $this->getElements('w:bookmarkStart/*');
        //$nodes = $this->getElements('w:body/*');
        //$nodes = $this->getElements('*');
        $nodes = $this->getElements('w:body/w:tbl/w:tr/w:tc/w:p/w:bookmarkStart');
        //dd($nodes);
        //dd($nodes->item(0)->getAttribute('w:name'));
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $cell_arguments = explode('_', $node->getAttribute('w:name'));

                $table_code = substr($cell_arguments[0], 1);
                $row_index = ltrim($cell_arguments[1], '0');
                if (isset($cell_arguments[2])) {
                    $column_index = ltrim($cell_arguments[2], '0');
                    var_dump(mb_strlen($column_index));

                }

            }
        }
    }

    public function getDomFromPath($path)
    {
        //dd($content);
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
}

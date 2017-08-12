<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 19.10.2016
 * Time: 7:35
 */

namespace App\Medinfo\Calculation;


abstract class ParseTree
{
    public $type;
    public $content;
    public $children; // normalized child list

    public function __construct($nodeType, $nodeContent)
    {
        $this->type = $nodeType;
        $this->content = $nodeContent;
        $this->children = new \SplDoublyLinkedList();
    }

    public function __toString() {
        return "<pre><" . $this->type . ', '. $this->content . ">" . PHP_EOL . "</pre>";
    }

    public function addChild(ParseTree $pt)
    {
        $this->children->push($pt);
    }

}
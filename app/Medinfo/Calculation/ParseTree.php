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
    public $parent;
    public $children = [];

    public function __construct($nodeType, $nodeContent)
    {
        $this->type = $nodeType;
        $this->content = $nodeContent;
    }

    public function __toString() {
        return "<pre><" . $this->type . ', '. $this->content . ">" . PHP_EOL . "</pre>";
    }

    public function setParent(ParseTree $node)
    {
        $this->parent = $node;
    }

    public function addChild(ParseTree $node, ParseTree $parent = null)
    {
        $this->children[] = $node;
        if (!is_null($parent)) {
            $this->parent = $parent;
        }
    }

    public function left()
    {
        if (isset($this->children[0])) {
            return $this->children[0];
        } else {
            return null;
        }
    }

    public function right()
    {
        if (isset($this->children[1])) {
            return $this->children[1];
        } else {
            return null;
        }
    }

    public function addLeft(ParseTree $node)
    {
        $this->children[0] = $node;
    }

    public function addRight(ParseTree $node)
    {
        $this->children[1] = $node;
    }

    public function unsetChildren()
    {
        $this->children = [];
    }

    public function dettachLeft()
    {
        $dettached = $this->children[0];
        unset($this->children[0]);
        return $dettached;
    }

    public function dettachRight()
    {
        $dettached = $this->children[1];
        unset($this->children[1]);
        return $dettached;
    }


}
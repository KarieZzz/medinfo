<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 10.09.2017
 * Time: 18:37
 */

namespace App\Medinfo\DSL;


use App\Document;
use App\Cell;

class ControlFunctionEvaluator
{
    public $document;
    public $pTree;
    public $properties;
    public $iterations;
    public $caStack = [];
    public $expr_node1;
    public $expr_node2;
    public $boolean_op;
    public $not_in_scope = false;
    public $valid;

    public function __construct(ParseTree $ptree, $properties, Document $document)
    {
        $this->pTree = $ptree;
        $this->properties = $properties;
        //dd($properties);
        $this->iterations = $properties['iterations'];
        $this->document = $document;
        $this->expr_node1 = $this->getArgument(1);
        $this->expr_node2 = $this->getArgument(1);
        $this->boolean_op = $this->getArgument(2)->content;
    }

    public function validateScope()
    {
        if ($this->properties['scope_documents']) {
            if ($this->document->dtype === $this->properties['incldocuments'][0]) {
                return true;
            } elseif($this->document->dtype === $this->properties['excldocuments'][0]) {
                return false;
            }
        }
        return true;
    }

    public function getArgument($index)
    {
        if (!$this->pTree->children[$index]->children[0] instanceof ParseTree) {
            throw new \Exception("Аргумент $index не найден");
        }
        return $this->pTree->children[$index]->children[0];
    }

    public function prepareCellValues()
    {
        foreach ($this->iterations as &$cell_adresses) {
            foreach ($cell_adresses as &$cell_adress) {
                $cell = Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                !$cell ? $value = 0 : $value = (float)$cell->value;
                $cell_adress['value'] = $value;
            }
        }
    }

    public function prepareCAstack()
    {
        $this->getCAnode($this->pTree);
    }

    public function getCAnode(ParseTree $parseTree)
    {
        $children  = $parseTree->children;
        if (count($children) > 0) {
            foreach ($children as $child) {
                if ($child->type == ControlFunctionLexer::CELLADRESS) {
                    $this->caStack[$child->content] = $child;
                }
                $this->getCAnode($child);
            }
        }
    }

    public function makeControl()
    {
        if (!$this->validateScope()) {
            $this->not_in_scope = true;
        }
        $this->prepareCellValues();
        $this->prepareCAstack();
        $result = [];
        $valid = true;
        $i = 0;
        foreach ($this->iterations as $code => $iteration) {

            foreach ($iteration as $cell_label => $props) {
                //$node = $caStack[$cell_label]['node'];
                $node = $this->caStack[$cell_label];
                $node->type = ControlFunctionLexer::NUMBER;
                $node->content = $props['value'];
                $result[$i]['cells'][] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            }
            //dd($this->expr_node1);
            $result[$i]['code'] = $code !== 0 ? $code : null;
            $result[$i]['left_part_value'] = $this->evaluate($this->expr_node1);
            $result[$i]['right_part_value'] = $this->evaluate($this->expr_node2);
            $result[$i]['deviation'] = abs($result[$i]['left_part_value'] - $result[$i]['right_part_value']);
            $result[$i]['valid'] = $this->compareArgs($result[$i]['left_part_value'], $result[$i]['right_part_value'], $this->boolean_op);
            $valid = $valid &&  $result[$i]['valid'];
            $i++;
        }
        $this->valid = $valid;
        return $result;
    }

    public function compareArgs($lp, $rp, $boolean)
    {
        $delta = 0.0001;
        // Если обе части выражения равны нулю - пропускаем проверку.
        if ($lp == 0 && $rp == 0) {
            return true;
        }
        switch ($boolean) {
            case '=' :
            case '==' :
                $result = abs($lp - $rp) < $delta ? true : false;
                //dd($rp);
                break;
            case '>' :
                $result = $lp > $rp;
                break;
            case '>=' :
                $result = $lp >= $rp;
                break;
            case '<' :
                $result = $lp < $rp;
                break;
            case '<=' :
                $result = $lp <= $rp;
                break;
            case '^' :
                $result = ($lp && $rp) || (!$lp && !$rp);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    public function evaluate(ParseTree $expr_root)
    {
        return $this->evaluateSubtree($expr_root);
    }

    public function evaluateSubtree(ParseTree $node)
    {
        //dump(ControlFunctionLexer::$tokenNames[$node->type]);
        if ($node->type === ControlFunctionLexer::NUMBER) {
            return $node->content;
        } elseif ($node->type === ControlFunctionLexer::NAME ) {
            if ($node->content == 'сумма') {
                $value = 0;
                foreach ($node->children as $child) {

                    if ($child->type === ControlFunctionLexer::NUMBER) {

                        $value += $child->content;
                    }
                }
            } elseif ($node->content == 'меньшее') {
                $values = [];
                foreach ($node->children as $child) {
                    if ($child->type === ControlFunctionLexer::NUMBER) {
                        $values[] = $child->content;
                    }
                }
                $value = min($values);

            } elseif ($node->content == 'большее') {
                $values = [];
                foreach ($node->children as $child) {
                    if ($child->type === ControlFunctionLexer::NUMBER) {
                        $values[] = $child->content;
                    }
                }
                $value = max($values);
            }
            return $value;
        } else {

            $left = $this->evaluateSubtree($node->left());
            $right = $this->evaluateSubtree($node->right());
            switch (ControlFunctionLexer::$tokenNames[$node->type]) {
                case 'PLUS' :
                    return $left + $right;
                    break;
                case 'MINUS' :
                    return $left - $right;
                    break;
                case 'MULTIPLY' :
                    return $left * $right;
                    break;
                case 'DIVIDE' :
                    //dump($right);
                    if ($right === 0) {
                        return 0;
                        break;
                    }
                    return $left / $right;
                    break;
            }
        }
        return null;
    }


}
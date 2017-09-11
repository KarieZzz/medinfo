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
    public $translator;
    public $document;
    public $pTree;
    public $caStack = [];
    public $expr_node1;
    public $expr_node2;
    public $boolean_op;

    public function __construct(ControlPtreeTranslator $translator, Document $document)
    {
        $this->translator = $translator;
        $this->pTree = $translator->parser->root;
        $this->document = $document;
        $this->expr_node1 = $this->pTree->children[0]->children[0];
        $this->expr_node2 = $this->pTree->children[1]->children[0];
        $this->boolean_op = $this->pTree->children[2]->children[0]->content;
    }

    public function prepareCellValues() {
        foreach ($this->translator->iterations as &$cell_adresses) {
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
        //$caStack = $this->translator->parser->celladressStack;
        foreach ($this->translator->iterations as &$iteration) {
            foreach ($iteration as $cell_label => $props) {
                //$node = $caStack[$cell_label]['node'];
                $node = $this->caStack[$cell_label];
                $node->type = ControlFunctionLexer::NUMBER;
                $node->content = $props['value'];
            }
            // TODO: Выполнить сравнение аргументов
            //dd($this->expr_node1);
            $iteration['left_part_value'] = $this->evaluate($this->expr_node1);
            $iteration['right_part_value'] = $this->evaluate($this->expr_node2);
            $iteration['deviation'] = $this->evaluate($this->expr_node2);
            $iteration['valid'] = $this->compareArgs($iteration['left_part_value'], $iteration['left_part_value'], $this->boolean_op);
        }
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
                $result = abs($lp - $rp) < $delta ? true : false;
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
        if (ControlFunctionLexer::$tokenNames[$node->type] === 'NUMBER') {
            return $node->content;
        } elseif (ControlFunctionLexer::$tokenNames[$node->type] === 'NAME') {
            if ($node->content == 'сумма') {
                $value = 0;
                foreach ($node->children as $child) {
                    if (ControlFunctionLexer::$tokenNames[$child->type] === 'NUMBER') {
                        $value += $child->content;
                    }
                }
            } elseif ($node->content == 'меньшее') {
                $values = [];
                foreach ($node->children as $child) {
                    if (ControlFunctionLexer::$tokenNames[$child->type] === 'NUMBER') {
                        $values[] = $child->content;
                    }
                }
                $value = min($values);
            } elseif ($node->content == 'большее') {
                $values = [];
                foreach ($node->children as $child) {
                    if (ControlFunctionLexer::$tokenNames[$child->type] === 'NUMBER') {
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
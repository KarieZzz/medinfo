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

class CalculationFunctionEvaluator
{
    public $document;
    public $pTree;
    public $properties;
    public $iterations;
    public $caStack = [];
    public $arguments;

    public function __construct(ParseTree $ptree, $properties, Document $document)
    {
        $this->pTree = $ptree;
        $this->properties = $properties;
        $this->document = $document;
        $this->setIterations();
        $this->setArguments();
    }

    public function setIterations()
    {
        $this->iterations = $this->properties['iterations'];
    }

    public function setArguments() { }

    public function evaluate()
    {
        $result[] = null;
        return $result;
    }

    public function getArgument($index)
    {
        if (!$this->pTree->children[$index-1]->children[0] instanceof ParseTree) {
            throw new \Exception("Аргумент $index не найден");
        }
        $this->arguments[$index] = $this->pTree->children[$index-1]->children[0];
    }

    public function prepareCellValues()
    {
        $dtype = $this->document->dtype;
        $ou_id = $this->document->ou_id;
        $period_id = $this->document->period_id;
        $form_id = $this->document->form_id;
        foreach ($this->iterations as &$cell_adresses) {
            foreach ($cell_adresses as &$cell_adress) {
                if ($cell_adress['ids']['f'] === $form_id) {
                    $cell = Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                } else {
                    $document = Document::OfTUPF($dtype, $ou_id, $period_id, $cell_adress['ids']['f'])->first();
                    $cell = $document ? Cell::OfDRC($document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']) : null;
                }
                !$cell ? $value = 0 : $value = (float)$cell->value;
                $cell_adress['value'] = $value;
            }
        }
    }


    public function prepareCAstack()
    {
        $this->getCAnode($this->pTree);
    }

    public function getCAnode(ParseTree $parseTree, $arg = 0)
    {
        $children  = $parseTree->children;
        if (count($children) > 0) {
            foreach ($children as $index => $child) {
                if ($child->type == ControlFunctionLexer::CELLADRESS) {
                    $this->caStack[$child->content] = $child;
                }
                $this->getCAnode($child, $arg);
            }
        }
    }

    public function convertCANodes(Array &$iteration)
    {
        $cells = [];
        property_exists($this, 'markOnlyFirstArg') ? $markOnlyFirstArg = true : $markOnlyFirstArg = false;
        foreach ($iteration as $cell_label => $props) {
            if (!array_key_exists($cell_label, $this->caStack)) {
                throw new \Exception("Ключ " . $cell_label . " не найден в стэке узлов адресов ячеек");
            }
            $node = $this->caStack[$cell_label];
            $node->type = ControlFunctionLexer::NUMBER;
            $node->content = $props['value'];
            if ($props['arg'] == 0) {
                $cells[] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            } elseif ($props['arg'] > 0 && $markOnlyFirstArg === false) {
                $cells[] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            }

        }
        return $cells;
    }

    public function compare($lp, $rp, $boolean)
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
            if (is_null($node->left())) {
                throw new \Exception('ParseTree узел слева в дереве AST пуст');
                //dd($this->properties);
            }
            $left = $this->evaluateSubtree($node->left());
            if (is_null($node->right())) {
                throw new \Exception('ParseTree узел справа в дереве AST пуст');
                //dd($this->properties);
            }
            $right = $this->evaluateSubtree($node->right());
            switch (ControlFunctionLexer::$tokenNames[$node->type]) {
                case 'PLUS' :
                    return $left + $right;
                case 'MINUS' :
                    return $left - $right;
                case 'MULTIPLY' :
                    return $left * $right;
                case 'DIVIDE' :
                    if ($right === 0) {
                        return 0;
                    }
                    return $left / $right;
                case 'DIVIDEMOD' :
                    if ($right === 0) {
                        return 0;
                    }
                    return fmod($left, $right);
            }
        }
        return null;
    }


}
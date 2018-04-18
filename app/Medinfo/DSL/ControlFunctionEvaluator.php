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
use App\Period;

class ControlFunctionEvaluator
{
    public $document;
    public $pTree;
    public $properties;
    public $iterations;
    public $caStack = [];
    public $arguments;
    public $not_in_scope = false;
    public $valid;

    public function __construct(ParseTree $ptree, $properties, Document $document)
    {
        $this->pTree = $ptree;
        $this->properties = $properties;
        $this->document = $document;
        $this->setIterations();
        $this->setArguments();
    }

    public function validateDocumentScope()
    {
        $exclude_by_type = false;
        $exclude_by_ou_id = false;
        if ($this->properties['scope_documents']) {
            if ($this->document->dtype === $this->properties['documents'][0]) {
                $exclude_by_type = false;
            } else {
                $exclude_by_type = true;
            }
        }
        if ($this->properties['scope_units']) {
            if (in_array($this->document->ou_id, $this->properties['units'])) {
                $exclude_by_ou_id = false;
            } else {
                $exclude_by_type = true;
            }
        }
        return $exclude_by_type xor $exclude_by_ou_id;
    }

    public function setIterations()
    {
        $this->iterations = $this->properties['iterations'];
    }

    public function setArguments() { }

    public function evaluate()
    {
        $result['l'] = null;
        $result['r'] = null;
        $result['d'] = null;
        $result['v'] = null;
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
        if ($this->properties['type'] === 1 ) {
            $this->setInformCellValues();
        } else {
            $this->setCellValuesArbitrary();
        }
    }

    public function setInformCellValues()
    {
        foreach ($this->iterations as &$cell_adresses) {
            foreach ($cell_adresses as &$cell_adress) {
                $cell = Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                !$cell ? $value = 0 : $value = (float)$cell->value;
                $cell_adress['value'] = $value;
            }
        }
    }

    public function setCellValuesArbitrary()
    {
        $dtype = $this->document->dtype;
        $ou_id = $this->document->ou_id;
        $period_id = $this->document->period_id;
        $form_id = $this->document->form_id;
        foreach ($this->iterations as &$cell_adresses) {
            foreach ($cell_adresses as &$cell_adress) {
                if ($cell_adress['ids']['f'] === $form_id && !isset($cell_adress['codes']['p'])) {
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

    public function getDocumentPeriod($code)
    {
        $current_period = Period::find($this->document->period_id);

        switch ($code) {
            case '-1' :

        }
    }

    public function makeControl()
    {
        $this->not_in_scope = $this->validateDocumentScope();
        $result = [];
        //dd($this->not_in_scope);
        if ($this->not_in_scope) {
            $result[0]['valid'] = true;
            $this->valid = true;
            return $result;
        }
        $this->prepareCellValues();
        $this->prepareCAstack();
        $valid = true;
        $i = 0;
        //dd($this->iterations);
        foreach ($this->iterations as $code => $iteration) {
            $cells = $this->convertCANodes($iteration);
            $result[$i]['cells'] = $cells;
            $result[$i]['code'] = $code !== 0 ? $code : null;
            $r = $this->evaluate();
            $result[$i]['left_part_value'] = $r['l'];
            $result[$i]['right_part_value'] = $r['r'];
            $result[$i]['deviation'] = $r['d'];
            $result[$i]['valid'] = $r['v'];
            $valid = $valid &&  $result[$i]['valid'];
            $i++;
        }
        $this->valid = $valid;
        return $result;
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

    public function multiplicity($number, $divider)
    {
        return fmod($number, $divider) == 0 ? true : false;
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
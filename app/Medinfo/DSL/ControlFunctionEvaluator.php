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
use App\PeriodPattern;

class ControlFunctionEvaluator
{
    public $document = null;
    public $period;
    public $pattern;
    public $form;
    public $pTree;
    public $properties;
    public $iterations;
    public $caStack = [];
    public $cellProperties = [];
    public $arguments;
    public $not_in_scope = false;
    public $valid;
    public $comment = [];
    public $related = false; // Является ли форма разрезом?

    public function __construct(ParseTree $ptree, $properties, Document $document = null)
    {
        $this->pTree = $ptree;
        $this->properties = $properties;
        if ($document) {
            $this->setDocument($document);
            //$this->document = $document;
            //$this->period = $this->document->period;
            //$this->pattern = $this->period->periodpattern;
        }
        $this->setIterations();
        $this->setArguments();
        $this->prepareCAstack();
        $this->prepareCellProperties();
    }

    public function setDocument(Document $document)
    {
        $this->document = $document;
        $this->period = $this->document->period;
        $this->pattern = $this->period->periodpattern;
        $this->form = $this->document->form;
        $this->related = $this->form->relation ?  true : false ;
        //$this->form_code = $this->related ? $this->document->form->form_code : null ;
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

    public function validateDocumentScope()
    {
        $exclude = [];
        if ($this->properties['scope_documents']) {
            if ($this->document->dtype === $this->properties['documents'][0]) {
                $exclude[] = 0;
            } else {
                $exclude[] = 1;
                $this->comment[] = "Данный контроль не применяется к этому типу документа";
            }
        }
        if ($this->properties['scope_units']) {
            if (in_array($this->document->ou_id, $this->properties['units'])) {
                $exclude[] = 0;
            } else {
                $exclude[] = 1;
                $this->comment[] = "Данный контроль не применяется к этой организационной единице";
            }
        }
        $slug = 'п' . trim($this->pattern->slug);
        //dd($slug);
        if ($this->properties['scope_periods']) {
            if (count($this->properties['incl_periods']) > 0 && in_array($slug, $this->properties['incl_periods']) ) {
                $exclude[] = 0;
            } elseif (count($this->properties['incl_periods']) == 0) {
                $exclude[] = 0;
            } else {
                $exclude[] = 1;
                $this->comment[] = "Данный контроль не применяется к документам этого отчетного периода";
            }
            if (count($this->properties['excl_periods']) > 0 && in_array($slug, $this->properties['excl_periods']) ) {
                $exclude[] = 1;
                $this->comment[] = "Данный контроль не применяется к документам этого отчетного периода";
            } else {
                $exclude[] = 0;
            }
        }
        if ($this->properties['scope_section']) {
            if ($this->document->form_id === $this->properties['section'] ) {
                $exclude[] = 0;
            } else {
                $exclude[] = 1;
                $this->comment[] = "Данный контроль не применяется к этому разрезу формы";
            }
        }
        // Для форм-разрезов межформенный контроль не выполняем
/*        if (in_array($this->document->form_id, $this->properties['relations']) && ($this->properties['type'] === 2)) {
            $exclude[] = 1;
            $this->comment[] = "Межформенные контроли не применяются к документам в разрезе форм";
        }*/
        //dd($exclude);
        if (array_sum($exclude) > 0) {
            return true;
        } else {
            return false;
        }
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
        //$period_id = $this->document->period_id;
        //$form_id = $this->document->form_id;

        foreach ($this->iterations as &$cell_adresses) {
            foreach ($cell_adresses as &$cell_adress) {
                //dd($cell_adress);
                if ($cell_adress['ids']['f'] === $this->form->id && !isset($cell_adress['codes']['p'])) {
                    $cell = Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                } elseif (in_array($cell_adress['ids']['f'], $this->properties['relations']) && !isset($cell_adress['codes']['p'])) {
                    $cell = Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                }
                elseif ( $cell_adress['this'] && !isset($cell_adress['codes']['p']) ) {
                    $cell = Cell::OfDRC($this->document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                } else {
                    //dd($form_id);
                    if (isset($cell_adress['codes']['p'])) {
                        $period = $this->getDocumentPeriod($cell_adress['codes']['p']);
                        //dd($period);
                        $period ? $document = Document::OfTUPF($dtype, $ou_id, $period->id, $cell_adress['ids']['f'])->first() : $document = null;
                    } else {
                        $document = Document::OfTUPF($dtype, $ou_id, $this->period->id, $cell_adress['ids']['f'])->first();
                        //dd($document);
                    }
                    if ($document) {
                        $cell_adress['doc_exists'] = true;
                        $cell = Cell::OfDRC($document->id, $cell_adress['ids']['r'], $cell_adress['ids']['c'])->first(['value']);
                    } else {
                        $cell_adress['doc_exists'] = false;
                        $cell = null;
                    }
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

    public function prepareCellProperties()
    {
        //dd($this->iterations);
        //for ($i = 0; $i < count($this->iterations); $i++) {
        foreach ($this->iterations as $code => $iteration) {
            $this->cellProperties[$code] = $this->setCellsProp($iteration);
        }
    }

    public function setCellsProp(Array $iteration)
    {
        $cells = [];
        property_exists($this, 'markOnlyFirstArg') ? $markOnlyFirstArg = true : $markOnlyFirstArg = false;
        foreach ($iteration as $cell_label => $props) {
            if (!array_key_exists($cell_label, $this->caStack)) {
                throw new \Exception("Ключ " . $cell_label . " не найден в стэке узлов адресов ячеек");
            }
            if ($props['arg'] == 0) {
                $cells[] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            } elseif ($props['arg'] > 0 && $markOnlyFirstArg === false) {
                $cells[] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            }
        }

        return $cells;
    }

    public function convertCANodes(Array &$iteration)
    {
        //$cells = [];
        //property_exists($this, 'markOnlyFirstArg') ? $markOnlyFirstArg = true : $markOnlyFirstArg = false;
        foreach ($iteration as $cell_label => $props) {
            if (!array_key_exists($cell_label, $this->caStack)) {
                throw new \Exception("Ключ " . $cell_label . " не найден в стэке узлов адресов ячеек");
            }
            $node = $this->caStack[$cell_label];
            $node->type = ControlFunctionLexer::NUMBER;
            $node->content = $props['value'];
/*            if ($props['arg'] == 0) {
                $cells[] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            } elseif ($props['arg'] > 0 && $markOnlyFirstArg === false) {
                $cells[] = ['row' => $props['ids']['r'], 'column' => $props['ids']['c']  ];
            }*/
        }
        //return $cells;
    }

    public function getDocumentPeriod($code)
    {
        $previous_period = null;
        switch ($code) {
            case '-1' :
                $previous_period = $this->getPreviousRelativePeriod();
                return $previous_period;
            case '0' :
                $periodicity = 1;
                $previous_period_pattern = PeriodPattern::Year()->first();
                break;
            case 'I' :
                $periodicity = 3;
                $previous_period_pattern = PeriodPattern::I()->first();
                break;
            case 'II' :
                $periodicity = 3;
                $previous_period_pattern = PeriodPattern::II()->first();
                break;
            case 'III' :
                $periodicity = 3;
                $previous_period_pattern = PeriodPattern::III()->first();
                break;
            case 'IV' :
                $periodicity = 3;
                $previous_period_pattern = PeriodPattern::IV()->first();
                break;
            case 'I+' :
                $periodicity = 4;
                $previous_period_pattern = PeriodPattern::Iplus()->first();
                break;
            case 'II+' :
                $periodicity = 4;
                $previous_period_pattern = PeriodPattern::IIplus()->first();
                break;
            case 'III+' :
                $periodicity = 4;
                $previous_period_pattern = PeriodPattern::IIIplus()->first();
                break;
            case 'IV+' :
                $periodicity = 4;
                $previous_period_pattern = PeriodPattern::IVplus()->first();
                break;
        }
        $previous_period = $this->getPreviousPeriod($previous_period_pattern, $periodicity);
        //dd($previous_period);
        return $previous_period;
    }

    public function getPreviousRelativePeriod()
    {
        switch ($this->pattern->periodicity) {
            case 1 : // годовые периоды
                $previous_period = Period::PreviousAnnual($this->period)->first();
                break;
            case 2 : // полугодовые периоды
                $previous_period = Period::PreviousSemiannual($this->period)->first();
                break;
            case 3 : // квартальные периоды
            case 4 :
                $previous_period = Period::PreviousQuarter($this->period)->first();
        }
        return $previous_period;
    }

    public function getPreviousPeriod(PeriodPattern $previous_period_pattern, int $periodicity)
    {
        if ($this->pattern->periodicity !== $periodicity) {
            $bool = '<=';
        } else {
            $bool = '<';
        }
        $previous_period = Period::whereHas('periodpattern', function ($query) use ($periodicity, $previous_period_pattern) {
            $query
                ->where('periodicity', $periodicity)
                ->where('begin', $previous_period_pattern->begin)
                ->where('end', $previous_period_pattern->end);
        })
            ->where('end_date', $bool , $this->period->end_date)
            ->orderBy('end_date', 'desc')
            ->first();
        return $previous_period;
    }

    public function makeControl()
    {
        if (!$this->document) {
            throw new \Exception("Документ для проведения контроля не определен");
        }
        $this->not_in_scope = $this->validateDocumentScope();
        $result = [];
        //dd($this->not_in_scope);
        if ($this->not_in_scope) {
            $result[0]['valid'] = true;
            $this->valid = true;
            return $result;
        }
        $this->prepareCellValues();
        $valid = true;
        $i = 0;
        //dd($this->arguments[1]);
        //dd($this->iterations);
        //dd($this->caStack);
        foreach ($this->iterations as $code => $iteration) {
            $this->convertCANodes($iteration);
            //$cells = $this->convertCANodes($iteration);
            //$result[$i]['cells'] = $cells;
            $result[$i]['cells'] = $this->cellProperties[$code];
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
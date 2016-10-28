<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2016
 * Time: 9:38
 */

namespace App\Medinfo\Lexer;
use App\Document;

class CompareControlInterpreter extends ControlInterpreter
{
    public $lpExpressionRoot; // node левая часть сравнения до интерации по неполным ссылкам
    public $rpExpressionRoot; // node правая часть сравнения до итерации по неполным ссылкам
    public $boolean; // знак сравнения в контроле
    public $lpStack = [];
    public $rpStack = [];

/*    public function __construct(ParseTree $root, int $form, int $table)
    {
        parent::__construct($root, $form, $table);
    }*/

    public function setArguments()
    {
        $this->lpExpressionRoot = $this->root->children[0];
        $this->rpExpressionRoot = $this->root->children[1];
        $this->boolean = $this->root->children[2]->tokens[0]->text;
        if (isset($this->root->children[3]->children[0]->tokens[0])) {
            $this->unitScope = $this->root->children[3]->children[0]->tokens[0]->text;
        }
        if (count($this->root->children[4]->children[0]->tokens)) {
            $this->iterationMode = $this->root->children[4]->tokens[0]->text == 'строки' ? 1 : 2;
            $this->setIterationRange($this->root->children[4]->children[0]->tokens);
        }
        $this->prepareReadable();
        $this->rewrite_summfunctions($this->lpExpressionRoot);
        $this->rewrite_summfunctions($this->rpExpressionRoot);
        if ($this->iterationMode) {
            foreach($this->iterationRange as $iteration) {
                $lpRootCopy = unserialize(serialize($this->lpExpressionRoot)); // clone не работает, нужно разобраться
                $rpRootCopy = unserialize(serialize($this->rpExpressionRoot));
                $this->lpStack[] = $this->fillIncompleteLinks($lpRootCopy, $iteration);
                $this->rpStack[] = $this->fillIncompleteLinks($rpRootCopy, $iteration);
            }
        }
    }

    public function prepareReadable()
    {
        $lp = $this->writeReadableCellAdresses($this->lpExpressionRoot);
        $rp = $this->writeReadableCellAdresses($this->rpExpressionRoot);
        $this->readableFormula = implode('', $lp) . ' ' . $this->boolean . ' ' . implode('', $rp);
        $this->results['boolean_sign'] = $this->boolean;
        $this->results['left_part_formula'] = implode('', $lp);
        $this->results['right_part_formula'] = implode('', $rp);
    }

    public function writeReadableCellAdresses(ParseTree $expression)
    {
        $expession_elements = [];
        foreach($expression->children as $element) {
            switch ($element->rule) {
                case  'celladress' :
                    $expession_elements = array_merge( $expession_elements, $this->rewriteCodes($element->tokens));
                    break;
                case 'operator' :
                case 'number' :
                    $expession_elements[] = $this->pad . $element->tokens[0]->text . $this->pad;
                    break;
                case 'summfunction' :
                    $expession_elements[] = 'сумма';
                    $expession_elements[] = '(';
                    $expession_elements = array_merge(
                        $expession_elements,
                        $this->rewriteCodes($element->children[0]->children[0]->tokens),
                        [' по '],
                        $this->rewriteCodes($element->children[0]->children[1]->tokens));
                    $expession_elements[] = ')';
                    break;
            }
        }
        return $expession_elements;
    }

    public function exec(Document $document)
    {
        $this->document = $document;
        $result = &$this->results['iterations'];
        $this->results['valid'] = true;
        if ($this->iterationMode) {
            for($i = 0; $i < count($this->iterationRange); $i++) {
                $this->rewrite_celladresses($this->lpStack[$i]);
                $this->rewrite_celladresses($this->rpStack[$i]);
                $lp_result = $this->calculate($this->lpStack[$i]);
                $rp_result = $this->calculate($this->rpStack[$i]);
                $result[$i]['left_part_value'] = $lp_result;
                $result[$i]['right_part_value'] = $rp_result;
                $result[$i]['deviation'] = abs(round($rp_result-$lp_result, 3));
                $result[$i]['valid'] = $this->chekoutRule($lp_result, $rp_result, $this->boolean);
                $this->results['valid'] = $this->results['valid'] && $result[$i]['valid'];
            }
        } else {
            $this->rewrite_celladresses($this->lpExpressionRoot);
            $this->rewrite_celladresses($this->rpExpressionRoot);
            $lp_result = $this->calculate($this->lpExpressionRoot);
            $rp_result = $this->calculate($this->rpExpressionRoot);
            $result[0]['left_part_value'] = $lp_result;
            $result[0]['right_part_value'] = $rp_result;
            $result[0]['deviation'] = abs(round($rp_result-$lp_result, 3));
            $result[0]['valid'] = $this->chekoutRule($lp_result, $rp_result, $this->boolean);
            $this->results['valid'] = $result[0]['valid'];
        }
        return $this->results;
    }

}
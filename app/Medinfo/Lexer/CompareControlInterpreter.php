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
            $this->unitScope = $this->setUnitScope($this->root->children[3]->children[0]->tokens[0]->text);
        }
        if (count($this->root->children[4]->children[0]->children)) {
            $this->iterationMode = $this->root->children[4]->tokens[0]->text == 'строки' ? 1 : 2;
            $this->setIterationRange($this->root->children[4]->children[0]->children);
        }
        $this->prepareReadable();
        $this->rewrite_summfunctions($this->lpExpressionRoot);
        $this->rewrite_summfunctions($this->rpExpressionRoot);

        if ($this->iterationMode) {
            foreach($this->iterationRange as $iteration) {
                //var_dump($iteration);
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
        //$this->results['left_part_formula'] = implode('', $lp);
        //$this->results['right_part_formula'] = implode('', $rp);
        $this->results['formula'] = $this->readableFormula;
    }

    public function exec(Document $document)
    {
        $this->document = $document;
        $this->results['valid'] = true;
        $this->results['not_in_scope'] = false;
        if (!$this->inScope()) {
            $this->results['not_in_scope'] = true;
            return $this->results;
        }
        $result = &$this->results['iterations'];
        $this->results['iteration_mode'] = $this->iterationMode;
        if ($this->iterationMode) {
            for($i = 0; $i < count($this->iterationRange); $i++) {
                $this->currentIteration = $i;
                $result[$i]['cells'] = [];
                $this->currentArgument = 1;
                $this->rewrite_celladresses($this->lpStack[$i]);
                $this->currentArgument = 2;
                $this->rewrite_celladresses($this->rpStack[$i]);
                $lp_result = $this->calculate($this->lpStack[$i]);
                $rp_result = $this->calculate($this->rpStack[$i]);
                if (count($this->errorStack) > 0) {
                    $result[$i]['errors'] = $this->errorStack;
                    $this->errors = array_merge($this->errors, $this->errorStack);
                    $this->errorStack = [];
                }
                $result[$i]['code'] = $this->iterationRange[$i];
                $result[$i]['left_part_value'] = $lp_result;
                $result[$i]['right_part_value'] = $rp_result;
                $result[$i]['deviation'] = abs(round($rp_result-$lp_result, 3));
                $result[$i]['valid'] = $this->chekoutRule($lp_result, $rp_result, $this->boolean);
                $this->results['valid'] = $this->results['valid'] && $result[$i]['valid'];
            }
        } else {
            $this->currentIteration = 0;
            $result[0]['cells'] = [];
            $this->currentArgument = 1;
            $this->reduce_minmaxfunctions($this->lpExpressionRoot);
            $this->rewrite_celladresses($this->lpExpressionRoot);
            $this->currentArgument = 2;
            $this->reduce_minmaxfunctions($this->rpExpressionRoot);
            $this->rewrite_celladresses($this->rpExpressionRoot);
            $lp_result = $this->calculate($this->lpExpressionRoot);
            $rp_result = $this->calculate($this->rpExpressionRoot);
            if (count($this->errorStack) > 0) {
                $result[0]['errors'] = $this->errorStack;
                $this->errors = array_merge($this->errors, $this->errorStack);
                $this->errorStack = [];
            }
            $result[0]['left_part_value'] = $lp_result;
            $result[0]['right_part_value'] = $rp_result;
            $result[0]['deviation'] = abs(round($rp_result-$lp_result, 3));
            $result[0]['valid'] = $this->chekoutRule($lp_result, $rp_result, $this->boolean);
            $this->results['valid'] = $result[0]['valid'];
        }
        if (count($this->errors) > 0) {
            $this->results['errors'] = $this->errors;
        }
        return $this->results;
    }

}
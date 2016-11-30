<?php

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
        if (isset($this->root->children[3]->children[0]->children[0])) {
            $this->unitScope = $this->setUnitScope($this->root->children[3]->children[0]);
        }
        if (count($this->root->children[4]->children[0]->children)) {
            $this->iterationMode = $this->root->children[4]->tokens[0]->text == 'строки' ? 1 : 2;
            $this->setIterationRange($this->root->children[4]->children[0]->children);
        }
        $this->prepareReadable();
        $translater = new ExpressionTranslater($this->form, $this->table);
        $translater->translate($this->lpExpressionRoot);
        $translater->translate($this->rpExpressionRoot);
        if ($this->iterationMode) {
            foreach($this->iterationRange as $iteration) {
                $this->currentIterationLink = $iteration;
                $lpRootCopy = unserialize(serialize($this->lpExpressionRoot)); // clone не работает, нужно разобраться
                $rpRootCopy = unserialize(serialize($this->rpExpressionRoot));
                $this->lpStack[] = $this->fillIncompleteLinks($lpRootCopy);
                $this->rpStack[] = $this->fillIncompleteLinks($rpRootCopy);

            }
        } else {
            $this->currentIterationLink = 0;
            $this->lpStack[] = $this->fillIncompleteLinks($this->lpExpressionRoot);
            $this->rpStack[] = $this->fillIncompleteLinks($this->rpExpressionRoot);

        }
        //dd($this->lpStack);
    }

    public function prepareReadable()
    {
        $lp = $this->writeReadableCellAdresses($this->lpExpressionRoot);
        $rp = $this->writeReadableCellAdresses($this->rpExpressionRoot);
        $this->readableFormula = implode('', $lp) . ' ' . $this->boolean . ' ' . implode('', $rp);
        $this->results['boolean_sign'] = $this->boolean;
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
        $this->results['iteration_mode'] = $this->iterationMode;
        if ($this->iterationMode) {
            for($i = 0; $i < count($this->iterationRange); $i++) {
                $this->execIteration($i);
            }
        } else {
            $this->execIteration(0);
        }
        if (count($this->errors) > 0) {
            $this->results['errors'] = $this->errors;
        }
        return $this->results;
    }

    public function execIteration($i)
    {
        $this->currentIteration = $i;
        $r = &$this->results['iterations'][$i];
        $r['cells'] = [];
        $this->currentArgument = 1;
        $this->reduce_minmaxfunctions($this->lpStack[$i]);
        $this->rewrite_celladresses($this->lpStack[$i]);
        $this->currentArgument = 2;
        $this->reduce_minmaxfunctions($this->rpStack[$i]);
        $this->rewrite_celladresses($this->rpStack[$i]);
        $lp_result = $this->calculate($this->lpStack[$i]);
        $rp_result = $this->calculate($this->rpStack[$i]);
        if (count($this->errorStack) > 0) {
            $r['errors'] = $this->errorStack;
            $this->errors = array_merge($this->errors, $this->errorStack);
            $this->errorStack = [];
        }
        if ($this->iterationMode) {
            $r['code'] = $this->iterationRange[$i];
        }
        $r['left_part_value'] = $lp_result;
        $r['right_part_value'] = $rp_result;
        $r['deviation'] = abs(round($rp_result-$lp_result, 3));
        $r['valid'] = $this->chekoutRule($lp_result, $rp_result, $this->boolean);
        $this->results['valid'] = $this->results['valid'] && $r['valid'];
    }

    protected function completeAdress($celladressNode)
    {
        $celladress = $celladressNode->tokens[0]->text;
        $matches = ExpressionTranslater::parseCelladress($celladress);
        //dd($matches);
        if (!$matches['f']) {
            $matches['f'] = $this->form->form_code;
        }
        if (!$matches['t']) {
            $matches['t'] = $this->table->table_code;
        }
        switch (true) {
            case !$matches['r'] && $this->iterationMode == 1 :
                $matches['r'] = $this->currentIterationLink;
                break;
            case !$matches['c'] && $this->iterationMode == 2 :
                $matches['c'] = $this->currentIterationLink;
                break;
            case !$matches['r'] && $this->iterationMode == 2 :
                throw new InterpreterException("Неполная ссылка по строке при режиме итерации по графам по ячейке " . $celladress);
                break;
            case !$matches['c'] && $this->iterationMode == 1 :
                throw new InterpreterException("Неполная ссылка по графе при режиме итерации по строкам по ячейке " . $celladress);
                break;
            case (!$matches['r'] || !$matches['c']) && $this->iterationMode == null :
                throw new InterpreterException("Неполная ссылка при отсутствии режима итерации. Адрес ячейки " . $celladress);
                break;
        }

        $celladress = 'Ф'. $matches['f'] . 'Т' . $matches['t'] . 'С'. $matches['r'] . 'Г' . $matches['c'];
        $celladressNode->tokens[0]->text = $celladress;
        return $celladress;
    }

}
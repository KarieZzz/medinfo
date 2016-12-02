<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2016
 * Time: 9:38
 */

namespace App\Medinfo\Lexer;
use App\Document;

class DependencyControlInterpreter extends CompareControlInterpreter
{
    public $lpExpressionRoot; // node левая часть сравнения до интерации по неполным ссылкам
    public $rpExpressionRoot; // node правая часть сравнения до итерации по неполным ссылкам
    public $boolean = '^'; // знак сравнения в контроле
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
        if (isset($this->root->children[3]->children[0]->children[0])) {
            $this->unitScope = $this->setUnitScope($this->root->children[3]->children[0]);
            //dd($this->unitScope);
        }
        if (count($this->root->children[3]->children[0]->children)) {
            $this->iterationMode = $this->root->children[3]->tokens[0]->text == 'строки' ? 1 : 2;
            $this->setIterationRange($this->root->children[3]->children[0]->children);
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
    }

    public function prepareReadable()
    {
        $lp = $this->writeReadableCellAdresses($this->lpExpressionRoot);
        $rp = $this->writeReadableCellAdresses($this->rpExpressionRoot);
        $this->readableFormula = implode('', $lp) . ' зависит от ' . implode('', $rp);
        $this->results['formula'] = $this->readableFormula;

    }

}
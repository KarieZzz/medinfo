<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.10.2016
 * Time: 9:38
 */

namespace App\Medinfo\Lexer;
use App\Document;

class InterannualControlInterpreter extends CompareControlInterpreter
{
    public $diapason; // первый аргумент диапазон ячеек
    public $threshold; // порог срабатывания - число (%)


    public function setArguments()
    {
        $this->diapason = $this->root->children[0];
        $this->threshold = $this->root->children[1];
        $this->prepareReadable();
        $this->rewrite_diapason($this->diapason);

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
        $readable_diapason = $this->writeReadableCellAdresses($this->diapason);
        $this->readableFormula = 'межгодовой контроль ячеек ' . implode('', $readable_diapason) ;
        $this->results['formula'] = $this->readableFormula;

    }

}
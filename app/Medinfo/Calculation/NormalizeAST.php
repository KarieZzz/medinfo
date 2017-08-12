<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 11.08.2017
 * Time: 14:09
 */

namespace App\Medinfo\Calculation;

use SplDoublyLinkedList;

class NormalizeAST extends Parser
{
    public $plusminusStack = [];
    public $multdivStack = [];
    public $leafStack = [];

    public function __construct(SplDoublyLinkedList $input)
    {
        parent::__construct($input);
        $this->tokenNames = CalculationFunctionLexer::$tokenNames;
    }

    public function selectPlusSubtactNodes()
    {
        $this->input->rewind();
        while ($this->input->valid()) {
            $current = $this->input->current();
            if ($current->type == CalculationFunctionLexer::OPERATOR && ($current->text === '+' || $current->text === '-')) {
                $newNode = new CalculationFunctionParseTree($current->type, $current->text);
                $this->plusminusStack[] = $newNode;
            }
            $this->input->next();
        }
    }

    public function selectMultDivNodes()
    {
        $this->input->rewind();
        while ($this->input->valid()) {
            $current = $this->input->current();
            if (!is_null($current) &&
                $current->type == CalculationFunctionLexer::OPERATOR
                && ($current->text === '*' || $current->text === '/')) {
                $newNode = new CalculationFunctionParseTree($current->type, $current->text);
                $this->multdivStack[] = $newNode;
                // Операнды слева и справа сразу присоединить к узлу
                $this->left($newNode);
                $this->right($newNode);

            }
            $this->input->next();
        }
    }

    public function selectLeafs()
    {
        $this->input->rewind();
        while ($this->input->valid()) {
            $current = $this->input->current();
            if (!is_null($current) && ($current->type == CalculationFunctionLexer::OPERAND || $current->type == CalculationFunctionLexer::COLUMNADRESS)) {
                $newNode = new CalculationFunctionParseTree($current->type, $current->text);
                $this->leafStack[] = $newNode;
            }
            $this->input->next();
        }
        $this->leafStack = array_merge($this->leafStack, $this->multdivStack);
    }

    public function composeAST()
    {
        $i = 0;
        $pnode = null;
        foreach ($this->plusminusStack as $pnode) {
            if ($this->root == null) {
                $this->root = $pnode;
            }
            if (isset($this->plusminusStack[$i + 1])) {
                $pnode->addChild($this->plusminusStack[$i + 1]);
                $pnode->addChild(array_shift($this->leafStack));
            } else {
                $pnode->addChild(array_shift($this->leafStack));
                $pnode->addChild(array_shift($this->leafStack));
            }
            $i++;
        }



        if ($this->root == null) {
            $i = 0;
            $mroot = null;
            foreach ($this->multdivStack as $mnode) {
                if ($this->root == null) {
                    $this->root = $mnode;
                } elseif ($mroot === null ) {
                    $mroot = $mnode;
                }

                if (isset($this->multdivStack[$i + 1])) {
                    $mnode->addChild($this->multdivStack[$i + 1]);
                }
                $i++;
            }
        }

/*        if (!is_null($pnode)) {
            $pnode->addChild($mroot);
        }*/
    }

/*    public function set()
    {
        $newNode = new CalculationFunctionParseTree($this->lookahead->type, $this->lookahead->text);
        $prevNode = $this->currentNode;
        if ($this->root == null) {
            $this->root = $newNode;
        } else {
            $this->currentNode->addChild($newNode);
        }
        $this->currentNode = $newNode;
        if ($this->lookahead->type == CalculationFunctionLexer::OPERATOR && ($this->lookahead->text === '+' || $this->lookahead->text === '-')) {
            $this->match(CalculationFunctionLexer::OPERATOR);
        }

        $this->currentNode = $prevNode;
    }*/

    public function left(ParseTree $node)
    {
        $leftKey = $this->input->key() - 1;
        $leftToken = $this->input->offsetGet($leftKey);
        $left = new CalculationFunctionParseTree($leftToken->type, $leftToken->text);
        $node->addChild($left);
        $this->input->offsetSet($leftKey, null);
    }

    public function right(ParseTree $node)
    {
        $rightKey = $this->input->key() + 1;
        $leftToken = $this->input->offsetGet($rightKey);
        $right = new CalculationFunctionParseTree($leftToken->type, $leftToken->text);
        $node->addChild($right);
        $this->input->offsetSet($rightKey, null);
    }

    public function getAST()
    {
        return $this->root;
    }

}
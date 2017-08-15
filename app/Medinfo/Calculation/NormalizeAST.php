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
    public $operators = ['+' , '-', '*', '/' ];
    public $attachSite;

    public function __construct(SplDoublyLinkedList $input)
    {
        parent::__construct($input);
        $this->input->pop(); // Удаляем токен EOF
        $this->tokenNames = CalculationFunctionLexer::$tokenNames;
    }


    public function setRootOffset()
    {
        foreach ($this->operators as $operator) {
            $this->selectRoot($operator);
            if ($this->root) {
                break;
            }
        }
    }

    public function selectRoot($operator)
    {
        $this->input->rewind();
        while ($this->input->valid()) {
            $current = $this->input->current();
            //var_dump($current);
            $offset = $this->input->key();
            if (!is_null($current) && $current->type == CalculationFunctionLexer::OPERATOR && $current->text === $operator) {
                $newNode = new CalculationFunctionParseTree($current->type, $current->text);
                $this->currentNode = $newNode;
                $this->rootOffset = $offset;
                $this->root = $newNode;
                break;
            }
            $this->input->next();
        }
        if ($this->root == null) {
            return false;
        } else {
            return true;
        }
    }

    public function leftTravers()
    {
        //dd($this->rootOffset);
        //dd($this->currentNode);
        $this->postorderTraversal($this->rootOffset -1);
    }

    public function postorderTraversal($offset)
    {
        // после завершения обхода дерева, заканчиваем обработку
        if ($offset === $this->rootOffset) {
            return;
        }
        if ($this->input->offsetExists($offset)) {
            $this->setNewLeftNode($this->input->offsetGet($offset));
            $this->manageNodes();
            //var_dump($this->currentNode);
            $this->postorderTraversal($offset - 1);
        } else {
            // после завершения обхода левой части дерева, переходим к правой
            $this->postorderTraversal($this->input->count() - 1);
        }
    }

    public function setNewLeftNode($token)
    {
        $newNode = new CalculationFunctionParseTree($token->type, $token->text);
        $this->currentNode->addLeft($newNode);
        $newNode->setParent($this->currentNode);
        $this->prevNode = $this->currentNode;
        $this->currentNode = $newNode;
    }

    // Если обнаружен оператор
    public function manageNodes() {

        if (!is_null($this->currentNode->parent) && $this->currentNode->type == CalculationFunctionLexer::OPERATOR
            && ($this->currentNode->content == '+' || $this->currentNode->content == '-' )
            && ($this->currentNode->parent->content !== '+' || $this->currentNode->parent->content !== '-' ))

        {

            $this->reverseSearchPlusSubtractNode($this->currentNode);
            $dettached = $this->attachSite->dettachLeft();
            dd($dettached);
            //$this->raisePlusSubtractOperator($this->currentNode);
        } elseif ($this->currentNode->type == CalculationFunctionLexer::OPERATOR && ($this->currentNode->content == '*' || $this->currentNode->content == '/' )) {
            $this->raiseOperator();
        } //else {
            //throw new \Exception('Ошибка построения AST. Некорректная последовательность токенов.');
        //}
    }

    public function raisePlusSubtractOperator($node)
{
    if (!is_null($node->parent) && $this->currentNode->type == CalculationFunctionLexer::OPERATOR
        && ($this->currentNode->content == '+' || $this->currentNode->content == '-' )) {
        return $node;
    } elseif (!is_null($node->parent)) {
        //$this->reverseSearchPlusSubtractNode($node);
    }
    return null;
}

    public function raiseOperator()
    {
        if ($this->prevNode->type == CalculationFunctionLexer::OPERAND) {
            $this->prevNode->parent->addLeft($this->currentNode); //
            $this->currentNode->setParent($this->prevNode->parent);
            $this->currentNode->addRight($this->prevNode); // Предыдущий узел дочерним справа от текущего узла
            $this->prevNode->setParent($this->currentNode); // Соответственно заменяется родительский узел
            $this->prevNode->unsetChildren(); // Соответственно заменяется родительский узел
        }
        //dd($this->root);
    }

    public function reverseSearchPlusSubtractNode(ParseTree $node)
    {
        //dump($node);
        if($node->parent == null) {
            return true;
        }
        if ($node->parent->type == CalculationFunctionLexer::OPERATOR && ($node->parent->content == '+' || $node->parent->content == '-' )) {
            $this->attachSite = $node->parent;
        }
        $this->reverseSearchPlusSubtractNode($node->parent);
        return ;
    }

    // То что в скобках
    public function enclosedTerms()
    {
        $this->input->rewind();

    }

    public function selectPlusSubtactNodes()
    {
        $this->input->rewind();
        while ($this->input->valid()) {
            $current = $this->input->current();
            if (!is_null($current) && $current->type == CalculationFunctionLexer::OPERATOR
                && ($current->text === '+' || $current->text === '-')) {
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
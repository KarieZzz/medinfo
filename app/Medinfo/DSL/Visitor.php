<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 15.08.2017
 * Time: 17:17
 */
/*
 * Класс не используется в данный момент
 * оставлен из-за того, что могут пригодиться некоторые функции
 *  */

namespace App\Medinfo\DSL;


class Visitor
{
    //
    public $parceTree;
    public $nodeType;
    public $value;
    public $left;
    public $right;
    public $method;

    public function __construct(ParseTree $parseTree)
    {
        $this->parceTree = $parseTree;
        $this->visit();
    }

    public function walkDescendNodes()
    {
        $left = $this->parceTree->left();
        if ($left && CalculationFunctionLexer::$tokenNames[$left->type] !== 'NUMBER') {
            $this->left = new Visitor($left);
        } else {
            $this->left = $left;
        }
        $right = $this->parceTree->right();
        if ($right && CalculationFunctionLexer::$tokenNames[$right->type] !== 'NUMBER') {
            $this->right = new Visitor($right);
        } else {
            $this->right = $right;
        }
    }

    public function visit()
    {
        $this->nodeType = CalculationFunctionLexer::$tokenNames[$this->parceTree->type];
        $this->value = $this->parceTree->content;
        switch (CalculationFunctionLexer::$tokenNames[$this->parceTree->type]) {
            case 'PLUS':
            case 'MINUS':
            case 'MULTIPLY':
            case 'DIVIDE':
                $this->method = 'visitBinaryOp';
                break;
            case 'NUMBER':
                $this->method = 'visitNumber';
                break;
            default:
                throw new \Exception("Неизвестный тип узла");
        }
    }

}
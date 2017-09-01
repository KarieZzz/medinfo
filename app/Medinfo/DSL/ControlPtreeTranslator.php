<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 01.09.2017
 * Time: 15:38
 */

namespace App\Medinfo\DSL;

use App\Form;
use App\Table;

class ControlPtreeTranslator
{
    public $parser;
    public $table;
    public $form;
    public $currentForm;
    public $withinform = true;

    public function __construct(ControlFunctionParser $parser, Table $table)
    {
        $this->parcer = $parser;
        $this->table = $table;
        $this->form = Form::find($table->form_id);
    }

    public function parseCellAdresses()
    {
        foreach ($this->parcer->celladressStack as $ca => &$props) {
            $props['codes'] = self::parseCelladress($ca);
            $props['ids']['f'] = $this->identifyControlType($props['codes']['f']);
            $props['ids']['t'] = $this->identifyTable($props['codes']['t'], $props['ids']['f']);

        }
        dd($this->parcer->celladressStack);
    }

    public static function parseCelladress($celladress)
    {
        $correct = preg_match('/(?:Ф(?P<f>[а-я0-9.-]*))?(?:Т(?P<t>[а-я0-9.-]*))?(?:С(?P<r>[0-9.-]*))?(?:Г(?P<c>\d{1,3}))?(?:П(?P<p>[01]))?/u', $celladress, $matches);
        if (!$correct) {
            throw new \Exception("Указан недопустимый адрес ячейки " . $celladress);
        }
        //dd($matches);
        return $matches;
    }

    public function identifyControlType($code)
    {
        if ($code == $this->form->form_code || empty($code) ) {
            return $this->form->id;
        } else {
            $form = Form::OfCode($code)->first();
            if (is_null($form)) {
                throw new \Exception("Форме с кодом $code не существует");
            }
            $this->currentForm = $form;
            $this->withinform = false;
            return $form->id;
        }
    }

    public function identifyTable($code, $form)
    {
        if ($code == $this->table->table_code || empty($code) ) {
            return $this->table->id;
        } else {
            //dd($form, $code);
            $table = Table::OfFormTableCode($form, $code)->first();
            if (is_null($table)) {
                throw new \Exception("В форме {$this->currentForm->form_code} не существует таблицы с кодом $code");
            }
            return $table->id;
        }
    }




}
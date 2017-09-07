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
use App\Row;
use App\Column;

class ControlPtreeTranslator
{
    public $parser;
    public $table;
    public $form;
    public $currentForm;
    public $withinform = true;
    public $vector = [];
    public $iterations = [];
    const ROWS = 1;
    const COLUMNS = 2;

    public function __construct(ControlFunctionParser $parser, Table $table)
    {
        $this->parser = $parser;
        $this->table = $table;
        $this->form = Form::find($table->form_id);
    }

    public function setParentNodesFromRoot()
    {
        self::setParentNode($this->parser->root);
    }

    public function parseCellAdresses()
    {
        foreach ($this->parser->celladressStack as $ca => &$props) {
            $props = $this->identifyCA($ca, $props);
        }
    }

    public function parseCellRanges()
    {
        foreach ($this->parser->cellrangeStack as &$range) {
            $first = $range['node']->children[0];
            $fprops = $this->identifyCA($first->content);
            $fprops['node'] = $first;
            $fprops['last'] = false;
            $last = $range['node']->children[1];
            $lprops = $this->identifyCA($last->content);
            $lprops['node'] = $last;
            $lprops['last'] = true;
            $this->validateRange($fprops['codes'], $lprops['codes']);
            $range = $this->inflateRangeMatrix($fprops, $lprops);
        }
    }

    public function parseRCRanges()
    {
        if ($this->vector === null) {
            $this->parser->rcStack = [];
            return;
        }
        foreach ($this->parser->rcStack as $rc ) {
            if ($this->vector[0] === self::ROWS) {
                $r = $this->identifyRow($rc, $this->table->id);
            } elseif ($this->vector[0] === self::COLUMNS) {
                $c = $this->identifyColumn($rc, $this->table->id);
            }
        }
        foreach ($this->parser->rcRangeStack as &$range) {
            $rcfunc = $range['node']->parent;
            $topcode = $range['node']->children[0]->content;
            $bottomcode = $range['node']->children[1]->content;
            if ($this->vector[0] === self::ROWS) {
                $toprow = $this->identifyRow($topcode,$this->table->id);
                $bottomrow = $this->identifyRow($bottomcode,$this->table->id);
                if ($toprow->row_index >= $bottomrow->row_index ) {
                    throw new \Exception("Указан неверный диапазон строк {$toprow->row_index} - {$bottomrow->row_index}");
                }
                for($i = $toprow->row_index; $i <= $bottomrow->row_index; $i++) {
                    //dump($i);
                    $intermediate = Row::OfTableRowIndex($this->table->id, $i)->first();
                    if (is_null($intermediate)) {
                        //throw new \Exception("В таблице id:{$this->table->id} не существует строка с индексом $i");
                        continue;
                    }
                    $new_ptnode = new ControlFunctionParseTree(ControlFunctionLexer::NUMBER, $intermediate->row_code);
                    $rcfunc->addChild($new_ptnode);
                    $this->parser->rcStack[] = $intermediate->row_code;
                }
            } elseif ($this->vector[0] === self::COLUMNS) {
                $topcolumn_index = (int)$topcode;
                $bottomcolumn_index = (int)$bottomcode;
                $topcolumn = Column::OfTableColumnIndex($this->table->id, $topcolumn_index)->first();
                if (is_null($topcolumn)) {
                    throw new \Exception("Ошибка в функции {$rcfunc->content}. В таблице id:{$this->table->id} не существует графа с кодом $topcode");
                }
                $bottomcolumn =Column::OfTableColumnIndex($this->table->id, $bottomcolumn_index)->first();
                if (is_null($bottomcolumn)) {
                    throw new \Exception("Ошибка в функции {$rcfunc->content}. В таблице id:{$this->table->id} не существует графа с кодом $bottomcode");
                }
                if ($topcolumn_index >= $bottomcolumn_index ) {
                    throw new \Exception("Указан неверный диапазон граф {$topcolumn_index} - {$bottomcolumn_index}");
                }
                for($i = $topcolumn_index; $i <= $bottomcolumn_index; $i++) {
                    $new_ptnode = new ControlFunctionParseTree(ControlFunctionLexer::NUMBER, $i);
                    $rcfunc->addChild($new_ptnode);
                    $this->parser->rcStack[] = $i;
                }
            }
            sort($this->parser->rcStack, SORT_NATURAL);
        }
    }

    public function prepareIteration()
    {
        $this->setParentNodesFromRoot();
        $this->parseCellAdresses();
        $this->parseCellRanges();
        $this->validateVector();
        $this->parseRCRanges();
        switch ($this->vector[0]) {
            case null :
                $this->iterations[] = $this->parser->celladressStack;
                break;
            case self::ROWS :
                if (count($this->parser->rcStack) > 0) {
                    $rows = Row::OfTable($this->table->id)->whereIn('row_code', $this->parser->rcStack)->orderBy('row_index')->get();
                } else {
                    $rows = Row::OfTable($this->table->id)->orderBy('row_index')->get();
                }
                foreach ($rows as $row) {
                    $iterations = $this->parser->celladressStack;
                    foreach ($iterations as &$ca) {
                        if ($ca['incomplete'] ) {
                            $ca['codes']['r'] = $row->row_code;
                            $ca['ids']['r'] = $row->id;
                            $ca['rowindex'] = $row->row_index;
                        }
                    }
                    $this->iterations[] = $iterations;
                }
                break;
            case self::COLUMNS :
                if (count($this->parser->rcStack) > 0) {
                    $columns = Column::OfTable($this->table->id)->whereIn('column_index', $this->parser->rcStack)->orderBy('column_index')->get();
                } else {
                    $columns = Column::OfTable($this->table->id)->orderBy('column_index')->get();
                }
                foreach ($columns as $column) {
                    $iterations = $this->parser->celladressStack;
                    foreach ($iterations as &$ca) {
                        if ($ca['incomplete'] ) {
                            $ca['codes']['c'] = $column->column_index;
                            $ca['ids']['c'] = $column->id;
                        }
                    }
                    $this->iterations[] = $iterations;
                }
                break;
        }
        return $this->iterations;
    }

    public function validateVector()
    {
        if (count($this->vector) === 0) {
            return null;
        }
        $this->vector = array_unique($this->vector);
        if (count($this->vector) > 1) {
            throw new \Exception('В одной функции контроля не допускается одновременной итерации и по строкам и по графам. ');
        }
        return $this->vector[0];
    }

    public function validateRange(array $topleft, array $bottomright)
    {
        if ($topleft['f'] !== $bottomright['f'] ) {
            throw new \Exception('Код формы, указанный в начале диапазона должен быть равен коду форму в конце диапазона');
        }
        if ($topleft['t'] !== $bottomright['t'] ) {
            throw new \Exception('Код таблицы, указанный в начале диапазона должен быть равен коду таблицы в конце диапазона');
        }
        if ($bottomright['r'] < $topleft['r'] ) {
            throw new \Exception("Код строки, указанный в конце диапазона ({$bottomright['r']}) не может предшествовавать коду строки в начале диапазона ({$topleft['r']})");
        }
        if ($bottomright['c'] < $topleft['c'] ) {
            throw new \Exception("Код графы, указанный в конце диапазона ({$bottomright['r']}) не может предшествовавать коду графы в начале диапазона ({$topleft['r']})");
        }
        if ( ($bottomright['r'] === $topleft['r'])&& ($bottomright['c'] === $topleft['c']) ) {
            throw new \Exception("Неверный диапазон. Начало и конец диапазона ссылаются на одну и туже ячейку (С{$topleft['r']}Г{$topleft['c']})");
        }
    }

    public function inflateRangeMatrix($fprops, $lprops)
    {
        $range = [];
        $r = $fprops['rowindex'];
        $j = 0;
        do {
            $incomplete = false;
            if ($r) {
                $row = Row::OfTableRowIndex($fprops['ids']['t'], $r)->first();
                $rowid = $row->id;
                $rowindex = $row->row_index;
                $rowcode = $row->row_code;
                $radrr = 'С' . $rowcode;
            } else {
                $rowid = null;
                $rowindex = null;
                $rowcode = '';
                $radrr = '';
                $incomplete = true;
            }
            $c = (int)$fprops['codes']['c'];
            do {
                if ($c !== 0) {
                    $column = Column::OfTableColumnIndex($fprops['ids']['t'], $c)->first();
                    $colid = $column->id;
                    $colindex = $column->column_index;
                    $cadrr = 'Г' . $colindex;
                } else {
                    $colid = null;
                    $colindex = '';
                    $cadrr = '';
                    $incomplete = true;
                }
                $f = $fprops['codes']['f'];
                $t = $fprops['codes']['t'];
                $f === '' ? $faddr = '' : $faddr = 'Ф' . $f;
                $t === '' ? $taddr = '' : $taddr = 'Т' . $t;
                $new_ptnode = new ControlFunctionParseTree(ControlFunctionLexer::CELLADRESS, $faddr . $taddr . $radrr . $cadrr);
                $range[$j]['node'] = $new_ptnode;
                $range[$j]['codes']['f'] = $f;
                $range[$j]['codes']['t'] = $t;
                $range[$j]['codes']['r'] = $rowcode;
                $range[$j]['codes']['c'] = $colindex;
                $range[$j]['ids']['f'] = $fprops['ids']['f'];
                $range[$j]['ids']['t'] = $fprops['ids']['t'];
                $range[$j]['ids']['r'] = $rowid;
                $range[$j]['ids']['c'] = $colid;
                $range[$j]['rowindex'] = $rowindex;
                $new_ptnode->parent = $fprops['node']->parent->parent;
                //$range->parent->addCild();
                $fprops['node']->parent->parent->addChild($range[$j]['node']);
                $this->parser->celladressStack[$faddr . $taddr . $radrr . $cadrr]['node'] = $new_ptnode;
                $this->parser->celladressStack[$faddr . $taddr . $radrr . $cadrr]['codes'] = $range[$j]['codes'];
                $this->parser->celladressStack[$faddr . $taddr . $radrr . $cadrr]['ids'] = $range[$j]['ids'];
                $this->parser->celladressStack[$faddr . $taddr . $radrr . $cadrr]['rowindex'] = $range[$j]['rowindex'];
                $this->parser->celladressStack[$faddr . $taddr . $radrr . $cadrr]['incomplete'] = $incomplete;
                $c++;
                $j++;
            } while($c <= (int)$lprops['codes']['c']);
            $r++;
        } while ($r <= $lprops['rowindex']);
        return $range;
    }

    public function identifyCA($ca, $props = [])
    {
        //dump($ca);
        $props['codes'] = self::parseCelladress($ca);
        $props['ids']['r'] = null;
        $props['rowindex'] = null;
        $props['incomplete'] = false;

        //dump($props);
        $props['ids']['f'] = $this->identifyControlType($props['codes']['f']);
        $props['ids']['t'] = $this->identifyTable($props['codes']['t'], $props['ids']['f']);
        $row = $this->identifyRow($props['codes']['r'], $props['ids']['t']);
        if ($row) {
            $props['ids']['r'] = $row->id;
            $props['rowindex'] = $row->row_index;
        }
        isset($props['codes']['c']) ?: $props['codes']['c'] = '';
        $props['ids']['c'] = $this->identifyColumn($props['codes']['c'], $props['ids']['t']);
        if ($props['ids']['r'] == null || $props['ids']['c'] == null) {
            $props['incomplete'] = true;
        }
        return $props;
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

    public function identifyRow($code, $table)
    {
        //dump($code ==='');
        if ($code ==='') {
            $this->vector[] = self::ROWS;
            return null;
        }
        $row = Row::OfTableRowCode($table, $code)->first();
        if (is_null($row)) {
            throw new \Exception("В таблице id:{$table} не существует строки с кодом $code");
        }
        return $row;
    }

    public function identifyColumn($code, $table)
    {
        //dump($code ==='');
        if ($code ==='') {
            $this->vector[] = self::COLUMNS;
            return null;
        }
        $column = Column::OfDataType()->OfTableColumnIndex($table, $code)->first();
        if (is_null($column)) {
            throw new \Exception("В таблице id:{$table} не существует графы для ввода данных с индексом $code");
        }
        return $column->id;
    }

    public static function setParentNode(ParseTree $node)
    {
        $children  = $node->children;
        if (count($children) > 0) {
            foreach ($children as $child) {
                $child->parent = $node;
                self::setParentNode($child);
            }
        }
    }
}
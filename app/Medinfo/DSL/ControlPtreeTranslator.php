<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 01.09.2017
 * Time: 15:38
 */

namespace App\Medinfo\DSL;

use App\Form;
use App\Period;
use App\Table;
use App\Row;
use App\Column;
use App\Unit;
use App\UnitGroup;

class ControlPtreeTranslator
{
    public $parser;
    public $table;
    public $form;
    public $currentForm;
    public $type = [];
    public $findex;
    public $scriptReadable;
    public $vector = [];
    //public $lightweightCAStack = [];
    public $scopeOfUnits = false;
    public $units = [];
    public $scopeOfDocuments = false;
    public $documents = [];
    public $scopeOfPeriods = false;
    public $incl_periods = [];
    public $excl_periods = [];
    public $iterations = [];
    const ROWS = 1;
    const COLUMNS = 2;

    public function __construct(ControlFunctionParser $parser, Table $table)
    {
        $this->parser = $parser;
        $this->table = $table;
        $this->form = Form::find($table->form_id);
        //dd($this->parser->cellrangeStack);
        //dd($this->parser->celladressStack);
    }

    public function makeReadable() {  }

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
        //dd($this->parser->cellrangeStack);
        foreach ($this->parser->cellrangeStack as &$range) {
            $first = $range['node']->children[0];
            $last = $range['node']->children[1];
            $range['node']->children = [];
            $fprops = $this->identifyCA($first->content);
            $fprops['node'] = $first;
            $fprops['arg'] = $range['arg'];
            $fprops['last'] = false;
            $lprops = $this->identifyCA($last->content);
            $lprops['node'] = $last;
            $lprops['arg'] = $range['arg'];
            $lprops['last'] = true;

            $cellrange_vector = $this->validateRange($fprops, $lprops);
            //dd($cellrange_vector);
            $range = $this->inflateRangeMatrix($fprops, $lprops, $cellrange_vector);
            //dd($range);
            //unset($range['node']->children[0]);
            //unset($range['node']->children[1]);
        }
    }

    public function parseRCRanges()
    {
        if (count($this->vector)=== 0) {
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
                        throw new \Exception("В таблице id:{$this->table->id} не существует строка с индексом $i");
                        //continue;
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

    public function parseGroupScopes()
    {
        $includes = [];
        $excludes = [];
        $include_periods =[];
        $exclude_periods = [];
        if (count($this->parser->includeGroupStack) > 0 ||  count($this->parser->excludeGroupStack) > 0) {
            $this->scopeOfUnits = true;
            foreach ($this->parser->includeGroupStack as $group_slug) {
                if (!in_array($group_slug, UnitGroup::$reserved_slugs)) {
                    $group = UnitGroup::OfSlug($group_slug)->first();
                    if (is_null($group)) {
                        throw new \Exception("Группа $group_slug не существует");
                    }
                    $includes = array_merge($includes, $group->members->pluck('ou_id')->toArray());
                } else {
                    $static = $this->parseStaticGroup($group_slug);
                    if ($static) {
                        $includes = array_merge($includes, $static['units']);
                        if(!is_null($static['dtype'])) {
                            $this->documents[] = $static['dtype'];
                        }
                        if(!is_null($static['period'])) {
                            $this->incl_periods[] = $static['period'];
                        }
                    }
                }
             }
             //dd($includes);
            foreach ($this->parser->excludeGroupStack as $group_slug) {
                if (!in_array($group_slug, UnitGroup::$reserved_slugs)) {
                    $group = UnitGroup::OfSlug($group_slug)->first();
                    if (is_null($group)) {
                        throw new \Exception("Группа $group_slug не существует");
                    }
                    $excludes = array_merge($excludes, $group->members->pluck('ou_id')->toArray());
                    //dd($group->members->pluck('id'));
                } else {
                    $static = $this->parseStaticGroup($group_slug);
                    if ($static) {
                        $excludes = array_merge($excludes, $static['units']);
                        if(!is_null($static['dtype'])) {
                            $this->documents[] = $static['dtype'] === 1 ? 2 : 1;
                        }
                        if(!is_null($static['period'])) {
                            $this->excl_periods[] = $static['period'];
                        }
                    }
                }
            }
            if (count($includes) === 0 ) {
                $includes = Unit::Active()->get()->pluck('id')->toArray();
            }
            //dd($includes);
            //dd($excludes);
            $this->units = array_diff($includes, $excludes);
            //dd($this->units);
            $this->documents = array_unique($this->documents);
            //dd($this->documents);
            if (count($this->documents) > 1 ) {
                throw new \Exception("Не допускается дублирование включения или исключения документа в контроль в соответствии с типом (первичные, сводные)");
            }
            //dd($this->excldocuments);
            $period_duplication = array_intersect($this->incl_periods, $this->excl_periods);
            if (count($period_duplication) > 0) {
                throw new \Exception("Не допускается дублирование включения или исключения в/из области видимости одних и тех же периодов");
            }
            $this->incl_periods = array_unique($this->incl_periods);
            $this->excl_periods = array_unique($this->excl_periods);

            //dd($this->incl_periods);
/*            $i = 1;
            foreach ($this->units as $u) {
                $unit = Unit::find($u);
                if ($unit) {
                    echo $i .'. ' . $unit->id . ' ' . $unit->unit_code . ' ' . $unit->unit_name . '</br>';
                } else {
                    echo $u . '</br>';
                }
                $i++;
            }*/
        }
    }
// TODO: Добавить обработку статических групп по периодам
    public function parseStaticGroup($static_group) {
        $units = [];
        $period = null;
        $dtype = null;
        switch ($static_group) {
            case UnitGroup::$reserved_slugs[1] :
                $this->scopeOfDocuments = true;
                $dtype = UnitGroup::PRIMARY;
                break;
            case UnitGroup::$reserved_slugs[2] :
                $this->scopeOfDocuments = true;
                $dtype = UnitGroup::AGGREGATE;
                break;
            case UnitGroup::$reserved_slugs[3] :
            case UnitGroup::$reserved_slugs[4] :
                $units = Unit::SubLegal()->get()->pluck('id')->toArray();
                break;
            case UnitGroup::$reserved_slugs[5] :
            case UnitGroup::$reserved_slugs[6] :
                $units = Unit::Legal()->get()->pluck('id')->toArray();
                break;
            case UnitGroup::$reserved_slugs[7] :
            case UnitGroup::$reserved_slugs[8] :
                $units = Unit::Territory()->get()->pluck('id')->toArray();
                break;
                // Периоды месячные
            case UnitGroup::$reserved_slugs[9]  :
            case UnitGroup::$reserved_slugs[10] :
            case UnitGroup::$reserved_slugs[11] :
            case UnitGroup::$reserved_slugs[12] :
            case UnitGroup::$reserved_slugs[13] :
            case UnitGroup::$reserved_slugs[14] :
            case UnitGroup::$reserved_slugs[15] :
            case UnitGroup::$reserved_slugs[16] :
            case UnitGroup::$reserved_slugs[17] :
            case UnitGroup::$reserved_slugs[18] :
            case UnitGroup::$reserved_slugs[19] :
            case UnitGroup::$reserved_slugs[20] :
            case UnitGroup::$reserved_slugs[21] :
                // Периоды квартальные
            case UnitGroup::$reserved_slugs[22] :
            case UnitGroup::$reserved_slugs[23] :
            case UnitGroup::$reserved_slugs[24] :
            case UnitGroup::$reserved_slugs[25] :
                $this->scopeOfPeriods = true;
                $period = $static_group;
                break;
            default:
                throw new \Exception("Группа $static_group не определена");
        }

        return compact('units', 'period', 'dtype');
    }

    public function parseFunctionIndex()
    {
        $this->findex = FunctionDispatcher::functionIndex($this->parser->root->content);
    }

    public function prepareIteration()
    {
        $this->makeReadable();
        $this->setParentNodesFromRoot();
        $this->parseFunctionIndex();
        $this->parseCellAdresses();
        $this->parseCellRanges();
        $this->validateVector();
        $this->parseRCRanges();
        $this->parseGroupScopes();
        //dd($this->vector);
        $lightweightCAStack = [];

        foreach ($this->parser->celladressStack as $caLabel => $caProps) {
            $lightweightCAStack[$caLabel]['arg'] = $caProps['arg'];
            $lightweightCAStack[$caLabel]['codes'] = $caProps['codes'];
            $lightweightCAStack[$caLabel]['ids'] = $caProps['ids'];
            //$lightweightCAStack[$caLabel]['rowindex'] = $caProps['rowindex'];
            $lightweightCAStack[$caLabel]['incomplete'] = $caProps['incomplete'];
        }
        if (count($this->vector)=== 0) {
            $this->iterations[] = $lightweightCAStack;
        } elseif ($this->vector[0] === self::ROWS) {
            // Если аргумент ограничивающий итерацию по строкам (строки(...)) не пустой, выбираем строки из диапазона
            if (count($this->parser->rcStack) > 0) {
                $rows = Row::OfTable($this->table->id)->whereIn('row_code', $this->parser->rcStack)->orderBy('row_index')->get();
            } else {
                $rows = Row::OfTable($this->table->id)->orderBy('row_index')->get();
            }
            foreach ($rows as $row) {
                //dd($row);
                foreach ($lightweightCAStack as $key => $ca) {
                    if ($ca['ids']['t'] === $this->table->id) {
                        $lightweightCAStack[$key]['codes']['r'] = $row->row_code;
                        $lightweightCAStack[$key]['ids']['r'] = $row->id;
                    } else {
                        $r = Row::OfTableRowCode($ca['ids']['t'], $row->row_code)->first();
                        if (is_null($r)) {
                            throw new \Exception("В таблице id:{$ca['ids']['t']} не существует строки с кодом {$row->row_code}");
                        }
                        $lightweightCAStack[$key]['codes']['r'] = $r->row_code;
                        $lightweightCAStack[$key]['ids']['r'] = $r->id;
                    }
                }
                $this->iterations[$row->row_code] = $lightweightCAStack;
            }
        }  elseif ($this->vector[0] === self::COLUMNS) {
            // Если аргумент ограничивающий итерацию по графам (графы(...)) не пустой, выбираем графы из диапазона
            if (count($this->parser->rcStack) > 0) {
                $columns = Column::OfTable($this->table->id)->OfDataType()->whereIn('column_index', $this->parser->rcStack)->orderBy('column_index')->get();
            } else {
                $columns = Column::OfTable($this->table->id)->OfDataType()->orderBy('column_index')->get();
            }
            foreach ($columns as $column) {
                //$iterations = $this->parser->argStack;
                foreach ($lightweightCAStack as $key => $ca) {
                    if ($ca['ids']['t'] === $this->table->id) {
                        $lightweightCAStack[$key]['codes']['c'] = $column->column_index;
                        $lightweightCAStack[$key]['ids']['c'] = $column->id;
                    } else {
                        $c = Column::OfTableColumnIndex($ca['ids']['t'], $column->column_index)->first();
                        if (is_null($c)) {
                            throw new \Exception("В таблице id:{$ca['ids']['t']} не существует графы с индексом {$column->column_index}");
                        }
                        $lightweightCAStack[$key]['codes']['r'] = $c->column_index;
                        $lightweightCAStack[$key]['ids']['r'] = $c->id;
                    }


                }
                $this->iterations[$column->column_index] = $lightweightCAStack;
            }
        }
        //dd($this->iterations);
        return $this->iterations;
    }

    public function getProperties()
    {
        $properties = [];
        if (property_exists($this, 'boolean_sign')) {
            $properties['boolean_sign'] = $this->boolean_sign;
        }
        $properties['iterations'] = $this->iterations;
        $properties['type'] = max($this->type);
        $properties['iteration_mode'] = isset($this->vector[0]) ? $this->vector[0] : null ;
        $properties['formula'] = $this->scriptReadable;
        $properties['function_id'] = $this->findex;
        $properties['function'] = FunctionDispatcher::$functionIndexes[$this->findex];
        $properties['scope_units'] = $this->scopeOfUnits;
        $properties['units'] = $this->units;
        $properties['scope_documents'] = $this->scopeOfDocuments;
        $properties['documents'] = $this->documents;
        $properties['scope_periods'] = $this->scopeOfPeriods;
        $properties['incl_periods'] = $this->incl_periods;
        $properties['excl_periods'] = $this->excl_periods;


        return $properties;
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
        $cellrange_vector = null;
        if ($topleft['codes']['f'] !== $bottomright['codes']['f'] ) {
            throw new \Exception('Код формы, указанный в начале диапазона должен быть равен коду форму в конце диапазона');
        }
        if ($topleft['codes']['t'] !== $bottomright['codes']['t'] ) {
            throw new \Exception('Код таблицы, указанный в начале диапазона должен быть равен коду таблицы в конце диапазона');
        }
        if ($bottomright['rowindex'] < $topleft['rowindex']) {
            throw new \Exception("Код строки, указанный в конце диапазона ({$bottomright['codes']['r']}) не может предшествовавать коду строки в начале диапазона ({$topleft['codes']['r']})");
        }
        if ($bottomright['codes']['c'] < $topleft['codes']['c'] ) {
            throw new \Exception("Код графы, указанный в конце диапазона ({$bottomright['codes']['c']}) не может предшествовавать коду графы в начале диапазона ({$topleft['codes']['c']})");
        }
        if ( ($bottomright['codes']['r'] === $topleft['codes']['r'])&& ($bottomright['codes']['c'] === $topleft['codes']['c']) ) {
            throw new \Exception("Неверный диапазон. Начало и конец диапазона ссылаются на одну и туже ячейку (С{$topleft['codes']['r']}Г{$topleft['codes']['c']})");
        }
        if (empty($bottomright['codes']['r']) && empty($topleft['codes']['r'])) {
            $cellrange_vector = 1;
        } elseif (empty($bottomright['codes']['r']) xor empty($topleft['codes']['r'])) {
            throw new \Exception("Неверный диапазон. В одном диапазоне не могут одновременно присутствовать полные и неполные ссылки (строки)");
        }
        if (empty($bottomright['codes']['c']) && empty($topleft['codes']['c'])) {
            $cellrange_vector = 2;
        } elseif (empty($bottomright['codes']['c']) xor empty($topleft['codes']['c'])) {
            throw new \Exception("Неверный диапазон. В одном диапазоне не могут одновременно присутствовать полные и неполные ссылки (графы)");
        }
        return $cellrange_vector;
    }

    public function inflateRangeMatrix($fprops, $lprops, $cellrange_vector = null)
    {
        if (isset($this->vector[0]) && (!is_null($cellrange_vector)) && ($this->vector[0] !== $cellrange_vector)) {
            throw new \Exception("Итерация в диапазаоне ячеек должна совпадать с итерацией в функции");
        }
        //dd($fprops);
        //dd($lprops);
        $range = [];
        switch ($cellrange_vector) {
            case 1: // по строкам (контроль граф)
                for ($j = 0, $first = (int)$fprops['codes']['c'], $last = (int)$lprops['codes']['c']; $j++, $first <= $last; $first++) {
                    $column = Column::OfTableColumnIndex($fprops['ids']['t'], $first)->first();
                    if (is_null($column)) {
                        continue;
                    }
                    $columnid = $column->id;
                    $colindex = $column->column_index;
                    $cadrr = 'Г' . $colindex;
                    $f = $fprops['codes']['f'];
                    $t = $fprops['codes']['t'];
                    $f === '' ? $faddr = '' : $faddr = 'Ф' . $f;
                    $t === '' ? $taddr = '' : $taddr = 'Т' . $t;
                    $key = $faddr . $taddr . $cadrr . "|" . $fprops['arg'];
                    $new_ptnode = new ControlFunctionParseTree(ControlFunctionLexer::CELLADRESS, $key);
                    $range[$j]['node'] = $new_ptnode;
                    $range[$j]['codes']['f'] = $f;
                    $range[$j]['codes']['t'] = $t;
                    $range[$j]['codes']['c'] = $colindex;
                    $range[$j]['ids']['f'] = $fprops['ids']['f'];
                    $range[$j]['ids']['t'] = $fprops['ids']['t'];
                    $range[$j]['ids']['c'] = $columnid;
                    $new_ptnode->parent = $fprops['node']->parent->parent;
                    //$range->parent->addCild();
                    $fprops['node']->parent->parent->addChild($range[$j]['node']);
                    $this->parser->celladressStack[$key]['node'] = $new_ptnode;
                    $this->parser->celladressStack[$key]['arg'] = $fprops['arg'];
                    $this->parser->celladressStack[$key]['codes'] = $range[$j]['codes'];
                    $this->parser->celladressStack[$key]['ids'] = $range[$j]['ids'];
                    $this->parser->celladressStack[$key]['incomplete'] = true;
                }
                break;
            case 2: // по графам (контроль строк)
                for ($j = 0, $first = $fprops['rowindex'], $last =  $lprops['rowindex']; $j++, $first <= $last; $first++) {
                    $row = Row::OfTableRowIndex($fprops['ids']['t'], $first)->first();
                    if (is_null($row)) {
                        throw new \Exception("В таблице отсутствует строка с индексом $first");
                        //continue;
                    }
                    $rowid = $row->id;
                    $rowindex = $row->row_index;
                    $rowcode = $row->row_code;
                    $radrr = 'С' . $rowcode;
                    $f = $fprops['codes']['f'];
                    $t = $fprops['codes']['t'];
                    $f === '' ? $faddr = '' : $faddr = 'Ф' . $f;
                    $t === '' ? $taddr = '' : $taddr = 'Т' . $t;
                    $key = $faddr . $taddr . $radrr . "|" . $fprops['arg'];
                    $new_ptnode = new ControlFunctionParseTree(ControlFunctionLexer::CELLADRESS, $key );
                    $range[$j]['node'] = $new_ptnode;
                    $range[$j]['codes']['f'] = $f;
                    $range[$j]['codes']['t'] = $t;
                    $range[$j]['codes']['r'] = $rowcode;
                    $range[$j]['ids']['f'] = $fprops['ids']['f'];
                    $range[$j]['ids']['t'] = $fprops['ids']['t'];
                    $range[$j]['ids']['r'] = $rowid;
                    $range[$j]['rowindex'] = $rowindex;
                    $new_ptnode->parent = $fprops['node']->parent->parent;
                    //$range->parent->addCild();
                    $fprops['node']->parent->parent->addChild($range[$j]['node']);
                    $this->parser->celladressStack[$key]['node'] = $new_ptnode;
                    $this->parser->celladressStack[$key]['arg'] = $fprops['arg'];
                    $this->parser->celladressStack[$key]['codes'] = $range[$j]['codes'];
                    $this->parser->celladressStack[$key]['ids'] = $range[$j]['ids'];
                    $this->parser->celladressStack[$key]['rowindex'] = $range[$j]['rowindex'];
                    $this->parser->celladressStack[$key]['incomplete'] = true;
                }
                break;
            case null:
                $j = 0;
                for ($cfirst = (int)$fprops['codes']['c'], $clast = (int)$lprops['codes']['c']; $cfirst <= $clast; $cfirst++) {
                    $column = Column::OfTableColumnIndex($fprops['ids']['t'], $cfirst)->first();
                    if (is_null($column)) {
                        continue;
                    }
                    $columnid = $column->id;
                    $colindex = $column->column_index;
                    $cadrr = 'Г' . $colindex;
                    $f = $fprops['codes']['f'];
                    $t = $fprops['codes']['t'];
                    $f === '' ? $faddr = '' : $faddr = 'Ф' . $f;
                    $t === '' ? $taddr = '' : $taddr = 'Т' . $t;
                    for ($rfirst = $fprops['rowindex'], $rlast =  $lprops['rowindex']; $rfirst <= $rlast; $rfirst++) {
                        $row = Row::OfTableRowIndex($fprops['ids']['t'], $rfirst)->first();
                        $rowid = $row->id;
                        $rowindex = $row->row_index;
                        $rowcode = $row->row_code;
                        $radrr = 'С' . $rowcode;
                        $f = $fprops['codes']['f'];
                        $t = $fprops['codes']['t'];
                        $f === '' ? $faddr = '' : $faddr = 'Ф' . $f;
                        $t === '' ? $taddr = '' : $taddr = 'Т' . $t;
                        $key = $faddr . $taddr . $radrr . $cadrr . "|" . $fprops['arg'];
                        $new_ptnode = new ControlFunctionParseTree(ControlFunctionLexer::CELLADRESS, $key);
                        $range[$j]['node'] = $new_ptnode;
                        $range[$j]['codes']['f'] = $f;
                        $range[$j]['codes']['t'] = $t;
                        $range[$j]['codes']['r'] = $rowcode;
                        $range[$j]['codes']['c'] = $colindex;

                        $range[$j]['ids']['f'] = $fprops['ids']['f'];
                        $range[$j]['ids']['t'] = $fprops['ids']['t'];
                        $range[$j]['ids']['r'] = $rowid;
                        $range[$j]['ids']['c'] = $columnid;
                        $range[$j]['rowindex'] = $rowindex;
                        $new_ptnode->parent = $fprops['node']->parent->parent;
                        //$range->parent->addCild();
                        $fprops['node']->parent->parent->addChild($range[$j]['node']);
                        $this->parser->celladressStack[$key]['node'] = $new_ptnode;
                        $this->parser->celladressStack[$key]['arg'] = $fprops['arg'];
                        $this->parser->celladressStack[$key]['codes'] = $range[$j]['codes'];
                        $this->parser->celladressStack[$key]['ids'] = $range[$j]['ids'];
                        $this->parser->celladressStack[$key]['rowindex'] = $range[$j]['rowindex'];
                        $this->parser->celladressStack[$key]['incomplete'] = false;
                        $j++;
                    }
                }
                break;
        }
/*        $r = $fprops['rowindex'];
        //dd($r);
        //dd( $lprops['rowindex']);
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
                $column = Column::OfTableColumnIndex($fprops['ids']['t'], $c)->first();
                if (is_null($column)) {
                    $c++;
                    continue;
                }
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
        } while ($r <= $lprops['rowindex']);*/
        //dd($range);
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
        $props['ids']['f'] = $this->identifyControlType($props['codes']);
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
/*        isset($props['codes']['p']) ?: $props['codes']['p'] = '';
        $this->identifyPeriod($props['codes']['p']);*/
        return $props;
    }

    public static function parseCelladress($celladress)
    {
        $correct = preg_match('/(?:Ф(?P<f>[а-я0-9.\-]*))?(?:Т(?P<t>[а-я0-9.\-]*))?(?:С(?P<r>[0-9.\-]*))?(?:Г(?P<c>\d{1,3}))?(?:П(?P<p>[0-9.\-\+IV]*))?/u', $celladress, $matches);
        if (!$correct) {
            throw new \Exception("Указан недопустимый адрес ячейки " . $celladress);
        }
        //dd($matches);
        return $matches;
    }

    public function identifyControlType($codes)
    {
        if ($this->findex == 3 || $this->findex == 4 || $this->findex == 19) {
            $this->type[] = (int)\App\DicCfunctionType::InterPeriod()->first(['code'])->code;
            return $this->form->id;
        } elseif (($codes['f'] == $this->form->form_code || empty($codes['f'])) && !isset($codes['p'])) {
            $this->type[] = (int)\App\DicCfunctionType::InForm()->first(['code'])->code;
            return $this->form->id;
        } elseif (($codes['f'] == $this->form->form_code || empty($codes['f'])) && isset($codes['p']))  {
            $this->type[] = (int)\App\DicCfunctionType::InterForm()->first(['code'])->code;
            return $this->form->id;
        } else {
            //dd($codes);
            $form = Form::OfCode($codes['f'])->first();
            if (is_null($form)) {
                throw new \Exception("Формы с кодом {$codes['f']} не существует");
            }
            $this->currentForm = $form;
            $this->type[] = (int)\App\DicCfunctionType::InterForm()->first(['code'])->code;

            return $form->id;
        }
    }

    public function identifyTable($code, $form)
    {
        if (($code == $this->table->table_code && $form == $this->table->form_id) || empty($code) ) {
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

    public function identifyRow($code, $table_id)
    {
        //dump($code ==='');
        if ($code ==='') {
            $this->vector[] = self::ROWS;
            return null;
        }
        $row = Row::OfTableRowCode($table_id, $code)->first();
        if (is_null($row)) {
            $table = $this->getTableInfo($table_id);
            $form = $table->form()->first();
            throw new \Exception("В таблице id:{$table_id} (($table->table_code) {$table->table_name} в форме {$form->form_code}) не существует строки с кодом $code");
        }
        return $row;
    }

    public function identifyColumn($code, $table_id)
    {
        //dump($code ==='');
        if ($code ==='') {
            $this->vector[] = self::COLUMNS;
            return null;
        }
        $column = Column::OfDataType()->OfTableColumnIndex($table_id, $code)->first();
        if (is_null($column)) {
            $table = $this->getTableInfo($table_id);
            $form = $table->form()->first();
            throw new \Exception("В таблице id:{$table_id} (($table->table_code) {$table->table_name} в форме {$form->form_code}) не существует графы для ввода данных с индексом $code");
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

    public function getTableInfo($table_id)
    {
        if ($this->table->id === $table_id) {
            return $this->table;
        } else {
            $table = Table::find($table_id);
            return $table;
        }
    }
}
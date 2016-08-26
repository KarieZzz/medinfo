<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 05.08.2016
 * Time: 16:33
 */

namespace App\Medinfo;

use App\Document;
use App\ControlCashe;
use App\Form;
use App\Column;
use App\Table;
use App\Cell;
use Carbon\Carbon;

class TableControlMM
{
    public $doc_id;
    public $form;
    public $table;
    public $rows;
    public $columns;
    private $force_reload = false;
    private $value_cashe;
    public static $control_type = array(1 => 'втк', 2 => 'вфк', 3 => 'мфк', 4 => 'кгр', 5 => 'квс' );
    public static $control_type_readable = array('втк' => 'внутритабличный контроль', 'вфк' => 'внутриформенный контроль', 'мфк' => 'межформенный контроль' );
    public static $boolean_sign = [0 => '^', 1 => '>', 2 => '>=', 3 => '==', 4 => '<=', 5 => '<'];
    public static $boolean_readable = array('^' => 'существует', '>' => 'больше', '>=' => 'больше или равно' , '==' => 'равно', '<=' => 'меньше или равно', '<' => 'меньше');
    public static $number_sign = [1 => '+', -1 => '-'];

    public function __construct($doc_id = null, $table_id = null)
    {
        if (!$doc_id) {
            throw new Exception("Для выполнения контроля по таблице необходимо указать Id документа");
        }
        if (!$table_id) {
            throw new Exception("Для выполнения контроля по таблице необходимо указать Id таблицы");
        }
        $this->doc_id = $doc_id;
        $this->form = Form::find(Document::find($doc_id)->form_id);
        $this->table = Table::find($table_id);
        $this->rows = \DB::table('rows')
            ->where('table_id', $this->table->id)
            ->where('deleted', 0)
            ->where('medinfo_id', '<>', 0)
            ->orderBy('row_index')->get();
        // Для контроля выбираем только графы со вводом данных
        $this->columns = \DB::table('columns')
            ->where('table_id', $this->table->id)
            ->where('deleted', 0)
            ->where('medinfo_type', 4)
            ->where('medinfo_id', '<>', 0)
            ->orderBy('column_index')->get();
    }

    public function setForceReload($state = false)
    {
        $this->force_reload = $state;
    }

    private function CashedProtocolActual()
    {
        $updated_at =  $this->dataUpdatedAt();
        $cahed_at = $this->protocolCashedAt();
        return $cahed_at->gt($updated_at);
    }

/*    public function comparePeriods()
    {
        //$rows = $this->table->getRows();
        //$cols = $this->table->getColumns();
        $rows = $this->table->rows->where('deleted', 0)->sortBy('row_index');
        $cols = $this->table->columns->where('deleted', 0)->sortBy('column_index');
        $validation_protocol = array();
        $i = 0;
        foreach ($rows as $row) {
            $j = 0;
            $columns = array();
            foreach ($cols as $col) {
                if ($col['content'] == 'data') {
                    if (!$this->doc_id) {
                        $cell_addr = 'O' . $this->ou_id . 'F' . $this->form_id . 'T' . $this->table_mid . 'R' . $row['row_id'] . 'C' . $col['col_id'] . 'P' . $this->period;
                        $v = new ValidateCellByMi($cell_addr);
                        $columns[$col['col_id']]['result'] = $v->check_prev_period();
                        $columns[$col['col_id']]['protocol'] = $v->getProtocol();
                    }
                    else {
                        $cell_addr = 'D' . $this->doc_id . 'T' . $this->table_mid . 'R' . $row['row_id'] . 'C' . $col['col_id'];
                        echo $cell_addr;
                        $v = new ComparePrevPeriod($cell_addr);
                        var_dump($v);
                        $columns[$col['col_id']]['result'] = $v->checkoutCell();
                        $columns[$col['col_id']]['protocol'] = $v->getProtocol();
                    }
                }
            }
            $validation_protocol[$i]['row_id'] = $row['row_id'];
            $validation_protocol[$i]['row_number'] = $row['row_code'];
            $validation_protocol[$i]['columns'] = $columns;
            $i++;
        }
        return $validation_protocol;
    }*/

    private function casheValues()
    {
        $q = "SELECT r.id row_id, c.id col_id, v.value FROM rows r
	      JOIN columns c ON r.table_id = c.table_id
          JOIN statdata v ON v.row_id = r.id AND v.col_id = c.id
          WHERE r.table_id = 10 AND v.doc_id = 7011";
        $this->value_cashe = collect(\DB::select($q));
    }

    private function getValue(int $row, int $column)
    {
        $cell = $this->value_cashe->where('row_id', $row)->where('col_id', $column);
        if (count($cell) == 0) {
            return 0;
        } else {
            return floatval($cell->first()->value);
        }
    }

    public function takeAllBatchControls()
    {
        if ($this->CashedProtocolActual() && !$this->force_reload) {
            $protocol = $this->loadProtocol();
            return $protocol;
        } else {
            $protocol['intable'] = $this->InTableRowControl();
            $protocol['inform'] = $this->InFormRowControl();
            $protocol['inreport'] = $this->InReportRowControl();
            $protocol['columns'] = $this->ColumnControl();
            $protocol['table_id'] = $this->table->id;
            $protocol['cashed'] = false;
            if ( $protocol['intable']['no_rules']
                && $protocol['inform']['no_rules']
                && $protocol['inreport']['no_rules']
                && $protocol['columns']['no_rules'] )
            {
                $protocol['no_rules'] = true;
            } else {
                $protocol['no_rules'] = false;
            }
            if ( $protocol['intable']['valid']
                && $protocol['inform']['valid']
                && $protocol['inreport']['valid']
                && $protocol['columns']['valid'] )
            {
                $protocol['valid'] = true;
            } else {
                $protocol['valid'] = false;
            }
            $this->cashProtocol($protocol);
            return $protocol;
        }
    }

    public function InTableRowControl() {
        $ctype_id = 1; // Внутритабличный контроль
        $protocol['valid'] = true;
        // Итерация по контролируемым строкам с типом контроля "вт.к."
        $rules = $this->getRules($ctype_id);
        if (!$rules) {
            $protocol['no_rules'] = true;
            $protocol['valid'] = true;
            return $protocol;
        } else {
            $protocol['no_rules'] = false;
        }
        $i = 0;
        foreach ($rules as $rule) {
            $protocol[$i]['valid'] = true;
            // Итерация по столбцам текущей таблицы и выполнение правила к каждому из них
            foreach ($this->columns as $column) {
                $crows = $this->getControllingRows($ctype_id, $rule->relation, $column->medinfo_id);
                // Итерация по столбцам, включенным в текущее правило контроля ("корреспонденция граф") - при наличии правила
                if (count($crows) > 0) {
                    $protocol[$i][$column->column_index] = $this->getRowProtocol($ctype_id, $rule, $column, $crows);
                    $protocol[$i]['valid'] = $protocol[$i]['valid'] && $protocol[$i][$column->column_index]['valid'];
                }
            }
            $protocol[$i]['row_id'] = $rule->row_id;
            $protocol['valid'] = $protocol['valid'] && $protocol[$i]['valid'];
            $i++;
        }
        return $protocol;
    }

    public function InFormRowControl()
    {
        $ctype_id = 2; // Внутриформенный контроль
        $protocol['valid'] = true;
        // Итерация по контролируемым строкам с типом контроля "вф.к."
        $rules = $this->getRules($ctype_id);
        if (!$rules) {
            $protocol['no_rules'] = true;
            $protocol['valid'] = true;
            return $protocol;
        } else {
            $protocol['no_rules'] = false;
        }
        $i = 0;
        foreach ($rules as $rule) {
            $protocol[$i]['valid'] = true;
            // Итерация по столбцам текущей таблицы и выполнение правила к каждому из них
            foreach ($this->columns as $column) {
                $crows = $this->getControllingRows($ctype_id, $rule->relation, $column->medinfo_id);
                // Итерация по столбцам, включенным в текущее правило контроля ("корреспонденция граф") - при наличии правила
                if (count($crows) > 0) {
                    $protocol[$i][$column->column_index] = $this->getRowProtocol($ctype_id, $rule, $column, $crows);
                    $protocol[$i]['valid'] = $protocol[$i]['valid'] && $protocol[$i][$column->column_index]['valid'];
                }
            }
            $protocol[$i]['row_id'] = $rule->row_id;
            $protocol['valid'] = $protocol['valid'] && $protocol[$i]['valid'];
            $i++;
        }
        return $protocol;
    }

    public function InReportRowControl()
    {
        $rule_type = 3; // Межформенный контроль
        $protocol['valid'] = true;
        // Итерация по контролируемым строкам с типом контроля "вф.к."
        $rules = $this->getRules($rule_type);
        if (!$rules) {
            $protocol['no_rules'] = true;
            $protocol['valid'] = true;
            return $protocol;
        } else {
            $protocol['no_rules'] = false;
        }
        $i = 0;
        foreach ($rules as $rule) {
            $protocol[$i]['valid'] = true;
            // Итерация по столбцам текущей таблицы и выполнение правила к каждому из них
            foreach ($this->columns as $column) {
                $crows = $this->getControllingRows($rule_type, $rule->relation, $column->medinfo_id);
                // Итерация по столбцам, включенным в текущее правило контроля ("корреспонденция граф") - при наличии правила
                if (count($crows) > 0) {
                    $protocol[$i][$column->column_index] = $this->getRowProtocol($rule_type, $rule, $column, $crows);
                    $protocol[$i]['valid'] = $protocol[$i]['valid'] && $protocol[$i][$column->column_index]['valid'];
                }
            }
            $protocol[$i]['row_id'] = $rule->row_id;
            $protocol['valid'] = $protocol['valid'] && $protocol[$i]['valid'];
            $i++;
        }
        return $protocol;
    }

    public function ColumnControl()
    {
        $rule_type = 4;
        $row_protocol['valid'] = true;
        $q_rule = "SELECT relation FROM controlled_rows WHERE table_id = {$this->table->id} AND control_scope = $rule_type";
        //dd($q_rule);
        $rule = \DB::selectOne($q_rule);
        if (!$rule) {
            $row_protocol['no_rules'] = true;
            return $row_protocol;
        } else {
            $row_protocol['no_rules'] = false;
        }
        $q_columns_range = "SELECT first_col, first_col+count_col last_col FROM controlling_rows WHERE rec_id = {$rule->relation}";
        //echo $q_columns_range;
        $columns_range = \DB::selectOne($q_columns_range);
        $q_columns = "SELECT ccols.controlled, columns.id controlling, columns.column_index, ccols.boolean_sign, ccols.number_sign FROM controlled_columns ccols
          JOIN columns ON columns.medinfo_id = ccols.controlling AND columns.table_id = {$this->table->id}
          WHERE ccols.rec_id >= {$columns_range->first_col} AND ccols.rec_id < {$columns_range->last_col}";
        $ccols = collect(\DB::select($q_columns));
        $row_protocol['valid'] = true;
        $i = 0;
        foreach ($this->rows as $row) {
            $row_protocol[$i]['valid'] = true;
            foreach ($this->columns as $column) {
                //$column_protocol = [];
                $controlled = $ccols->where('controlled', $column->medinfo_id);
                if (count($controlled) > 0) {
                    $column_id = $column->id;
                    $cell = Cell::OfDTRC($this->doc_id, $this->table->id, $row->id, $column->id)->first();
                    //$left_part_value = $this->getValue($row->id, $column->id);

                    if (!is_null($cell)) {
                        $left_part_value = floatval($cell->value);
                    } else {
                        $left_part_value = 0;
                    }
                    $left_part_formula = $this->getIntoText($rule_type) . 'строка ' . $row->row_code . ' (' . $row->row_name . '),'
                        . ' г.' . $column->column_index . '(' . $column->column_name . ')';
                    //$controlling = $controlled->pluck('controlling');
                    $right_part_value = 0;
                    $right_part_formula = '(';
                    foreach ($controlled  as $ccell) {
                        //var_dump($ccell);
                        $boolean_sign = self::$boolean_sign[$ccell->boolean_sign];
                        $boolean_readable = self::$boolean_readable[self::$boolean_sign[$ccell->boolean_sign]];
                        $cell = Cell::OfDTRC($this->doc_id, $this->table->id, $row->id, $ccell->controlling)->first();
                        if (!is_null($cell)) {
                            $right_part_value += floatval(self::$number_sign[$ccell->number_sign] . $cell->value);
                        }
                        $right_part_formula .= ' ' . self::$number_sign[$ccell->number_sign] . 'г.' . $ccell->column_index;
                    }
                    $right_part_formula .= ' )';
                    $deviation = $left_part_value - $right_part_value;
                    $valid =
                    $column_protocol = compact('left_part_value', 'left_part_formula', 'right_part_value', 'right_part_formula',
                        'boolean_sign', 'boolean_readable', 'deviation', 'column_id');
                    $column_protocol['row_id'] = $row->id;
                    $column_protocol['valid'] = $this->chekoutRule($column_protocol);
                    $row_protocol[$i][] = $column_protocol;
                    $row_protocol[$i]['valid'] = $row_protocol[$i]['valid'] && $column_protocol['valid'];
                }
            }
            $row_protocol[$i]['row_id'] = $row->id;
            $row_protocol['valid'] = $row_protocol['valid'] && $row_protocol[$i]['valid'];
            $i++;
        }

        return $row_protocol;
    }

    public function inRowControl()
    {


    }

    private function getRules(int $rule_type)
    {
        $q_rules = "SELECT row_id, relation, rows.row_code, rows.row_name FROM controlled_rows
          JOIN rows ON rows.id = controlled_rows.row_id
          WHERE controlled_rows.table_id = {$this->table->id} AND controlled_rows.control_scope = $rule_type
          ORDER BY rows.row_index";
        return \DB::select($q_rules);
    }

    private function getRowProtocol(int $rule_type, $rule, $column, $crows)
    {
        $lcell = Cell::OfDTRC($this->doc_id, $this->table->id, $rule->row_id, $column->id)->first();
        if (!is_null($lcell)) {
            $row_protocol['left_part_value'] = floatval($lcell->value);
        } else {
            $row_protocol['left_part_value'] = 0;
        }
        $row_protocol['left_part_formula'] = $this->getIntoText($rule_type) . 'строка ' . $rule->row_code . ' (' . $rule->row_name . '),'
            . ' графа ' . $column->column_index . ' (' . $column->column_name . ')';
        $right_part_value = 0;
        $right_part_formula = '( ';
        foreach ($crows as $crow) {
            $row_protocol['boolean_sign'] = self::$boolean_sign[$crow->boolean_sign];
            $row_protocol['boolean_readable'] = self::$boolean_readable[self::$boolean_sign[$crow->boolean_sign]];
            $rcell = Cell::OfDTRC($this->doc_id, $crow->table_id, $crow->row_id, $crow->col_id)->first();
            if (!is_null($rcell)) {
                $right_part_value += floatval(self::$number_sign[$crow->number_sign] . $rcell->value);
            }

            $right_part_formula .= $this->getRightPart($rule_type, $crow);
        }
        $row_protocol['right_part_value'] = $right_part_value;
        $row_protocol['right_part_formula'] = $right_part_formula . ' )';
        $row_protocol['deviation'] = $row_protocol['left_part_value'] - $right_part_value;
        $row_protocol['row_id'] = $rule->row_id;
        $row_protocol['column_id'] = $column->id;
        $row_protocol['valid'] = $this->chekoutRule($row_protocol);
        return $row_protocol;
    }

    private function getIntoText($rule_type)
    {
        switch ($rule_type) {
            case 1:
                $intro = 'Внутритабличный контроль: ';
                break;
            case 2:
                $intro = 'Внутриформенный контроль: ';
                break;
            case 3:
                $intro = 'Межформенный контроль: ';
                break;
            case 4:
                $intro = 'Контроль граф (типовая методика): ';
                break;
            default:
                $intro = "Неизвестный тип контроля";
                break;
        }
        return $intro;
    }

    private function getRightPart($rule_type, $crow)
    {
        switch ($rule_type) {
            case 1:
                $part = ' ' . self::$number_sign[$crow->number_sign] . 'с.' . $crow->row_code . ',г.' . $crow->column_index;
                break;
            case 2:
                $part = ' ' . self::$number_sign[$crow->number_sign] . 'т.' . $crow->table_code . ',с.' . $crow->row_code
                    . ',г.' . $crow->column_index;
                break;
            case 3:
                $part = ' ' . self::$number_sign[$crow->number_sign] . 'ф.' . $crow->form_code . ',т.' . $crow->table_code
                    . ',с.' . $crow->row_code . ',г.' . $crow->column_index;
                break;
            default:
                $part = "Неизвестный тип контроля";
                break;
        }
        return $part;
    }

    // Итерация по контролирующим строкам внутри текущего правила
    private function getControllingRows(int $rule_type, int $relation, int $column)
    {
        switch ($rule_type) {
            case 1:
                $f_sign = '=';
                $t_sign = '=';
                break;
            case 2:
                $f_sign = '=';
                $t_sign = '<>';
                break;
            case 3:
                $f_sign = '<>';
                $t_sign = '<>';
                break;
            default:
                return null;
                break;
        }
        $q_crows = "SELECT r.row_id, columns.id col_id, r.table_id,
                  forms.form_code, tables.table_code, rows.row_code, columns.column_index,
                  c.boolean_sign, c.number_sign
                  FROM controlling_rows r
                  JOIN controlled_columns c ON c.rec_id >= r.first_col AND c.rec_id < r.first_col + r.count_col
                  JOIN forms ON forms.id = r.form_id
                  JOIN tables ON tables.id = r.table_id
                  JOIN rows ON rows.id = r.row_id
                  JOIN columns ON c.controlling = columns.medinfo_id AND columns.table_id = r.table_id
                  WHERE r.relation = {$relation} AND r.form_id $f_sign {$this->form->id} AND r.table_id $t_sign {$this->table->id} AND c.controlled = $column
                  ORDER BY rows.row_index";
        //dd($q_crows);
        return \DB::select($q_crows);
    }

    private function chekoutRule(array $condition)
    {
        $lp = $condition['left_part_value'];
        $rp = $condition['right_part_value'];
        // Если обе части выражения равны нулю - пропускаем проверку.
        if ($lp == 0 && $rp == 0) {
            return true;
        }
        switch ($condition['boolean_sign']) {
            case '==' :
                $result = $lp == $rp;
                break;
            case '>' :
                $result = $lp >$rp;
                break;
            case '>=' :
                $result = $lp >= $rp;
                break;
            case '<' :
                $result = $lp < $rp;
                break;
            case '<=' :
            $result = $lp <= $rp;
            break;
            case '^' :
                $result = ($lp && $rp) || (!$lp && !$rp);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    public function cashProtocol($protocol)
    {
        $protocol['cashed'] = true;
        $to_store = serialize($protocol);
        $ccashe = ControlCashe::firstOrCreate(['doc_id' => $this->doc_id, 'table_id' => $this->table->id]);
        $ccashe->control_cashe = $to_store;
        $ccashe->cashed_at = Carbon::now();
        $ccashe->save();
        return true;
    }

    public function loadProtocol()
    {
        return unserialize(ControlCashe::OfDocumentTable($this->doc_id, $this->table->id)->first(['control_cashe'])->control_cashe);
    }

    public function protocolCashedAt()
    {
        $protocol = ControlCashe::OfDocumentTable($this->doc_id, $this->table->id)->first(['cashed_at']);
        if ($protocol) {
            return $protocol->cashed_at;
        } else {
            // Возвращаем объект с заведомо старой датой
            return Carbon::create(1900, 1, 1);
        }

    }

    public function dataUpdatedAt()
    {
        if (!$this->doc_id || !$this->table->id) {
            throw new Exception("Не указан идентификатор таблицы для получения даты и времени сохранения данных");
        }
        $q = "SELECT MAX(updated_at) latest_edited FROM statdata WHERE doc_id = {$this->doc_id} AND table_id = {$this->table->id}";
        $updated_at = \DB::selectOne($q)->latest_edited;
        if ($updated_at) {
            return new Carbon($updated_at);
        } else {
            // Возвращаем объект с заведомо старой датой
            return Carbon::create(1900, 1, 1);
        }
    }

    public static function tableContainsData(int $document, int $table)
    {
        if (!$document || !$table) {
            throw new Exception("Не указан идентификатор документа/таблицы для проверки наличия данных");
        }
        $q = "SELECT SUM(value) sum_of_values FROM statdata WHERE doc_id = $document AND table_id = $table";
        $res = \DB::selectOne($q);
        if ($res->sum_of_values > 0 ) {
            return true;
        } else {
            return false;
        }
    }
}
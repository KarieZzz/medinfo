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
    public $ou_id;
    public $form;
    public $table;
    public $rows;
    public $columns;
    //public $period;
    //public $protocol;
    //public $batchprotocol;
    //public $count_of_checked_rows;
    //public $protocol_cashed = 1;
    public $protocol_updated_at = null;
    public $data_updated_at;
    private $force_reload = false;
    private $value_cashe;
    //private $table_correct = true;
    //public $data_source = 'statdata';
    //public $doc_source = 'documents';
    //public $doc_class = 'Document';
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
        // Для контроля выбираем только графы с данными
        $this->columns = \DB::table('columns')
            ->where('table_id', $this->table->id)
            ->where('deleted', 0)
            ->where('medinfo_type', 4)
            ->where('medinfo_id', '<>', 0)
            ->orderBy('column_index')->get();
    }

/*    public function setDocumentId($document_id = null)
    {
        $this->doc_id = $document_id;
    }*/
    // Идентификатор таблицы из Мединфо
/*    public function setTableMId($mid)
    {
        $this->table_mid = $mid;
    }*/

/*    public function setPeriodId($period_id)
    {
        $this->period = $period_id;
    }*/

/*    public function tableCheck()
    {
        if (!$this->tableContainsData()) {
            $this->protocol = null;
            $this->cashProtocol(null);
            $this->count_of_checked_rows = -1;
            $this->protocol_cashed = 0;
            return $this->count_of_checked_rows;
        } else {
            if ($this->checkRelevance()) {
                if (!$this->force_reload) {
                    $this->loadProtocol();
                    $decoded_protocol = json_decode($this->protocol);
                    $this->count_of_checked_rows = count($decoded_protocol);
                }
                else {
                    $this->createProtocol();
                }
            }
            else {
                $this->createProtocol();
            }
            return $this->count_of_checked_rows;
        }
    }*/

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

/*    public function createProtocol()
    {
        $rows = $this->table->rows->where('deleted', 0)->sortBy('row_index');
        $cols = $this->table->columns->where('deleted', 0)->sortBy('column_index');
        $validation_protocol = array();
        $i = 0;
        foreach ($rows as $row) {
            $j = 0;
            $columns = array();
            $row_check_result = true;
            foreach ($cols as $col) {
                $contentType = $col->getMedinfoContentType();
                if ($contentType == 'data') {
                    $cell_addr = 'O' . $this->ou_id . 'F' . $this->form_id . 'T' . $this->table_mid . 'R' . $row['row_id'] . 'C' . $col['col_id'] . 'P' . $this->period;
                    // TODO: Нужно периписать функцию контроля ячейки
                    $v = new ValidateCellByMi($cell_addr);
                    $cell_check_res = $v->checkoutCell();
                    if ($cell_check_res !== true && $cell_check_res !== null) {
                        $row_check_result = false;
                        $columns[$col['col_id']] = $v->getProtocol();
                    }
                }
            }
            if (!$row_check_result) {
                $validation_protocol[$i]['row_id'] = $row['row_id'];
                $validation_protocol[$i]['row_number'] = $row['row_code'];
                $validation_protocol[$i]['columns'] = $columns;
            }
            $i++;
        }
        //$validation_protocol['cashed'] = 0;
        $this->protocol = json_encode($validation_protocol);
        $this->cashProtocol($validation_protocol);
        $this->count_of_checked_rows = count($validation_protocol);
        $this->protocol_cashed = 0;
        return $this->count_of_checked_rows;
    }*/

    public function comparePeriods()
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
    }

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
            //$protocol['cashed'] = true;
            //dd($protocol);
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

/*    public function createEmptyProtocol()
    {
        $rows = $this->table->rows->where('deleted', 0)->sortBy('row_index');
        $cols = $this->table->columns->where('deleted', 0)->sortBy('column_index');
        $arr = array();
        foreach ($rows as $row) {
            $id = $row['row_code'];
            $columns = array();
            $arr[$id]['row_id'] = $row->id;
            $arr[$id]['row_number'] = $row->row_code;
            $arr[$id]['row_name'] = $row->row_name;
            $arr[$id]['row_correct'] = true;
            foreach ($cols as $col) {
                $contentType = $col->getMedinfoContentType();
                if ($contentType == 'data') {
                    $columns[$col['col_id']]['column_protocols'] = array();
                    $columns[$col['col_id']]['column_index'] = $col->column_index;
                    $columns[$col['col_id']]['column_correct'] = true;
                }
            }
            $arr[$id]['columns'] = $columns;
        }
        $this->batchprotocol = $arr;
        return $this->batchprotocol;
    }*/
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

/*    public function batchIntableRowControl() {
        $ctype = 'втк';
        $ctype_id = 1;
        $q= "SELECT
                rows.id r,
                columns.id c,
                rows.row_code controlled_row_number,
                rows.row_name controlled_row_name,
                d.value left_part,
                controlled_columns.fk summation,
                dd.value right_part,
                controlled_columns.fr boolean_sign,
                controlling_rows.ol3 controlling_row_id,
                rrows.row_code controlling_row_number,
                c.column_index controlling_column_number
            FROM rows join columns on rows.table_id = columns.table_id
            left join statdata d on rows.id = d.row_id and columns.id = d.col_id and d.doc_id = {$this->doc_id} and d.table_id = {$this->table->id}
            join controlled_rows on rows.medinfo_id = controlled_rows.ol3 and rows.table_id = controlled_rows.nl2
            left join controlling_rows on rule.rl1235 = controlling_rows.rl1235 and rule.nl2 = controlling_rows.nl2 and controlling_rows.ol3 <> rows.ol
            left join rows rrows on controlling_rows.ol3 = rrows.medinfo_id and rrows.table_id = controlling_rows.nl2
            join controlled_columns on controlled_columns.rec_id >= controlling_rows.plf
            and controlled_columns.rec_id < controlling_rows.plf + controlling_rows.clf and controlled_columns.ol4_ = l4.ol
            join columns ccolumns on ccolumns.table_id = {$this->table->table_id} and ccolumns.medinfo_id = controlled_columns.ol4
            left join statdata dd on controlling_rows.ol3 = dd.row_mid and ccols.ol4 = dd.col_mid and dd.doc_id = {$this->doc_id}
            and dd.table_mid = controlling_rows.nl2
            where 1 and l3.nl2 = {$this->table_mid} and l4.trg = 4 and rule.fmk = {$ctype_id} order by l3.ks, l4.ol, ll3.ks, ccols.ol4;";
        //echo $q;
        $res = $this->dba->query($q);
        $r = $res->fetch_all();
        $protocol = array();
        foreach ($r as $cell ) {
            $row_id = $cell[2];
            $protocol[$row_id]['row_id'] = $cell[0];
            $protocol[$row_id]['row_number'] = $cell[2];
            $protocol[$row_id]['row_name'] = $cell[3];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['left_part'] = (float)$cell[4];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['rule_intro'] = 'внутритабличный контроль по методике';
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$cell[8]]['summation'] = $cell[5];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$cell[8]]['right_part'] = $cell[6];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$cell[8]]['bool_sign'] = $cell[7];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$cell[8]]['crow_number'] = $cell[9];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$cell[8]]['ccol_number'] = $cell[10];
        }
        $this->set_row_protocol($protocol, $ctype);
        return $protocol;
    }*/

/*    public function batchInformRowControl() {
        $ctype = 'вфк';
        $ctype_id = 2;
        $q = "SELECT
            l3.row_id r,l4.column_id c,
            l3.a7 controlled_row_number,
            l3.name controlled_row_name,
            d.value left_part,
            ccols.fk summation,
            dd.value right_part,
            ccols.fr boolean_sign,
            t.table_code,
            crows.ol3 controlling_row_id,
            ll3.a7 controlling_row_number,
            c.column_index controlling_column_number
        FROM l3 join l4 on l3.nl2 = l4.nl2
        left join {$this->data_source} d on l3.ol = d.row_mid and l4.ol=d.col_mid and d.doc_id={$this->doc_id} and d.table_mid={$this->table_mid}
        join l1235_ rule on l3.ol = rule.ol3 and l3.nl2=rule.nl2
        left join l12345 crows on rule.rl1235 = crows.rl1235 and rule.nl1 = crows.nl1 and rule.nl2 <> crows.nl2
        left join mi_table t on crows.nl2 = t.medinfo_id
        left join l3 ll3 on crows.ol3 = ll3.ol and ll3.nl2=crows.nl2
        join lf ccols on ccols.rec_id >= crows.plf and ccols.rec_id < crows.plf+crows.clf and ccols.ol4_ = l4.ol
        join mi_columns c on c.table_id = t.table_id and c.medinfo_id = ccols.ol4
        left join {$this->data_source} dd  on crows.ol3 = dd.row_mid and ccols.ol4 = dd.col_mid and dd.doc_id={$this->doc_id} and dd.table_mid=crows.nl2
        where l3.nl2={$this->table_mid} and l4.trg =4 and rule.fmk = {$ctype_id} order by l3.ks, l4.ol, t.table_code, ll3.ks;";
        //echo $q;
        $res = $this->dba->query($q);
        $r = $res->fetch_all();
        $protocol = array();
        foreach ($r as $cell ) {
            $row_id = $cell[2];
            $protocol[$row_id]['row_id'] = $cell[0];
            $protocol[$row_id]['row_number'] = $cell[2];
            $protocol[$row_id]['row_name'] = $cell[3];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['left_part'] = (float)$cell[4];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['rule_intro'] = 'внутриформенный контроль по методике';
            $ccel_id = $cell[8].'_'. $cell[9] . '_' . $cell[11];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['summation'] = $cell[5];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['right_part'] = $cell[6];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['bool_sign'] = $cell[7];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['crow_tablecode'] = $cell[8];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['crow_number'] = $cell[10];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['ccol_number'] = $cell[11];
        }
        $this->set_row_protocol($protocol, $ctype);
        return $protocol;
    }*/

/*    public function batchInreportRowControl() {
        $ctype = 'мфк';
        $ctype_id = 3;
        if (!$this->doc_id) {
            $q = "SELECT l3.ol r,l4.ol c, trim(l3.a7) controlled_row_number, trim(l3.name) controlled_row_name, d.value left_part,
            ccols.fk summation, dd.value right_part, ccols.fr boolean_sign, trim(mi_form.form_code) form_code, trim(mi_table.table_code) table_code,
            crows.ol3 controlling_row_id, trim(ll3.a7) controlling_row_number, ccols.ol4 controlling_column_number
            FROM l3 join l4 on l3.nl2 = l4.nl2
            left join l02345k0 d on l3.ol = d.ol3 and l4.ol=d.ol4 and d.nl0={$this->ou_id} and d.nl2={$this->table_mid}
            join l1235_ rule on l3.ol = rule.ol3 and l3.nl2=rule.nl2
            left join l12345 crows on rule.rl1235 = crows.rl1235 and rule.nl1 <> crows.nl1 and rule.nl2 <> crows.nl2
            left join mi_form on crows.nl1 = mi_form.form_id
            left join mi_table on crows.nl2 = mi_table.table_id
            left join l3 ll3 on crows.ol3 = ll3.ol and ll3.nl2=crows.nl2
            join lf ccols on  ccols.rec_id >= crows.plf and ccols.rec_id < crows.plf+crows.clf
            left join l02345k0 dd  on crows.ol3 = dd.ol3 and ccols.ol4 = dd.ol4 and dd.nl0={$this->ou_id} and dd.nl2=crows.nl2
            where l3.nl2={$this->table_mid} and l4.trg =4 and rule.fmk = {$ctype_id} and ccols.ol4_ = l4.ol
            order by l3.ks, l4.ol, mi_form.form_code, mi_table.table_code, ll3.ks;";
        }
        else {
            $a = new $this->doc_class($this->doc_id);
            $q = "SELECT
                l3.row_id r,l4.column_id c,
                l3.a7 controlled_row_number,
                l3.name controlled_row_name,
                d.value left_part,
                ccols.fk summation,
                dd.value right_part,
                ccols.fr boolean_sign,
                mi_form.form_code form_code,
                t.table_code table_code,
                crows.ol3 controlling_row_id,
                ll3.a7 controlling_row_number,
                c.column_index controlling_column_number
            FROM l3 join l4 on l3.nl2 = l4.nl2
            left join {$this->data_source} d on l3.ol = d.row_mid and l4.ol=d.col_mid and d.doc_id={$this->doc_id} and d.table_mid={$this->table_mid}
            join l1235_ rule on l3.ol = rule.ol3 and l3.nl2=rule.nl2
            left join l12345 crows on rule.rl1235 = crows.rl1235 and rule.nl1 <> crows.nl1 and rule.nl2 <> crows.nl2
            left join mi_form on crows.nl1 = mi_form.form_id
            left join mi_table t on crows.nl2 = t.medinfo_id
            left join l3 ll3 on crows.ol3 = ll3.ol and ll3.nl2=crows.nl2
            join lf ccols on ccols.rec_id >= crows.plf and ccols.rec_id < crows.plf+crows.clf
            join mi_columns c on c.table_id = t.table_id and c.medinfo_id = ccols.ol4
            left join {$this->doc_source} doc on doc.ou_id={$a->getOuId()} and doc.form_id = mi_form.form_id and doc.period_id = '{$this->period}'
            left join {$this->data_source} dd on dd.doc_id = doc.doc_id and crows.ol3 = dd.row_mid and ccols.ol4 = dd.col_mid and dd.table_mid=crows.nl2
            where l3.nl2={$this->table_mid} and l4.trg =4 and rule.fmk = {$ctype_id} and ccols.ol4_ = l4.ol
            order by l3.ks, l4.ol, mi_form.form_code, t.table_code, ll3.ks;";
        }
        //echo $q;
        $res = $this->dba->query($q);
        $r = $res->fetch_all();
        $protocol = array();
        foreach ($r as $cell ) {
            $row_id = $cell[2];
            $protocol[$row_id]['row_id'] = $cell[0];
            $protocol[$row_id]['row_number'] = $cell[2];
            $protocol[$row_id]['row_name'] = $cell[3];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['left_part'] = (float)$cell[4];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['rule_intro'] = 'межформенный контроль по методике';
            $ccel_id = $cell[8].'_'. $cell[9] . '_' . $cell[10]. '_' . $cell[12];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['summation'] = $cell[5];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['right_part'] = $cell[6];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['bool_sign'] = $cell[7];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['crow_formcode'] = $cell[8];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['crow_tablecode'] = $cell[9];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['crow_number'] = $cell[11];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['ccol_number'] = $cell[12];
        }
        $this->set_row_protocol($protocol, $ctype);
        return $protocol;
    }*/

/*    public function batchColumnControl() {
        $ctype = 'кгр';
        $ctype_id = 4;
        if (!$this->doc_id) {
            $q = "select l3.ol, l4.ol controlled_col, trim(l3.a7) controlled_row_number,  trim(l3.name) controlled_row_name, ccols.ol4 controlling_col, d.value lpart,
            ccols.fr boolean_sign, ccols.fk summation, dd.value rpart, ccols.ol4 controlling_column_number
            from l3 join l4 on l3.nl2 = l4.nl2
            left join l02345k0 d on l3.ol = d.ol3 and l4.ol=d.ol4 and d.nl0={$this->ou_id} and d.nl2={$this->table_mid}
            join lf ccols on ccols.rec_id >= (select plf from l12345 rule where rule.nl2 = {$this->table_mid} and rule.ol3 = 0)
            and ccols.rec_id < (select plf+clf from l12345 rule where rule.nl2 = {$this->table_mid} and rule.ol3 = 0) and ccols.ol4_ = l4.ol
            left join l02345k0 dd  on l3.ol = dd.ol3 and ccols.ol4 = dd.ol4 and dd.nl0={$this->ou_id} and dd.nl2={$this->table_mid}
            where l3.nl2 = {$this->table_mid} and l4.trg ={$ctype_id}
            order by l3.ks, l4.ol, ccols.ol4;";
        }
        else {
            $q = "select
              l3.row_id,
              l4.column_id controlled_col,
              l3.a7 controlled_row_number,
              l3.name controlled_row_name,
              c.column_index controlling_col,
              d.value lpart,
              ccols.fr boolean_sign,
              ccols.fk summation,
              dd.value rpart
            from l3 join l4 on l3.nl2 = l4.nl2
            left join {$this->data_source} d on l3.ol = d.row_mid and l4.ol=d.col_mid and d.doc_id={$this->doc_id} and d.table_mid={$this->table_mid}
            join lf ccols on ccols.rec_id >= (select plf from l12345 rule where rule.nl2 = {$this->table_mid} and rule.ol3 = 0)
            and ccols.rec_id < (select plf+clf from l12345 rule where rule.nl2 = {$this->table_mid} and rule.ol3 = 0) and ccols.ol4_ = l4.ol
            join mi_columns c on c.table_id = {$this->table->table_id} and c.medinfo_id = ccols.ol4
            left join {$this->data_source} dd  on l3.ol = dd.row_mid and ccols.ol4 = dd.col_mid and dd.doc_id={$this->doc_id} and dd.table_mid={$this->table_mid}
            where l3.nl2 = {$this->table_mid} and l4.trg ={$ctype_id}
            order by l3.ks, l4.ol, ccols.ol4;";
        }
        //echo $q;
        $res = $this->dba->query($q);
        $r = $res->fetch_all();
        $protocol = array();
        foreach ($r as $cell ) {
            $row_id = $cell[2];
            $protocol[$row_id]['row_id'] = $row_id;
            $protocol[$row_id]['row_number'] = $cell[2];
            $protocol[$row_id]['row_name'] = $cell[3];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['left_part'] = (float)$cell[5];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['rule_intro'] = 'Контроль графы (типовая методика)';
            $ccel_id = $cell[4];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['summation'] = $cell[7];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['right_part'] = $cell[8];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['bool_sign'] = $cell[6];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['crow_number'] = $cell[2];
            $protocol[$row_id]['columns'][$cell[1]][$ctype]['controlling_cells'][$ccel_id]['ccol_number'] = $cell[4];
        }
        $this->set_row_protocol($protocol, $ctype);
        return $protocol;
    }*/

    private function set_row_protocol($protocol, $ctype)
    {
        if (!$this->batchprotocol) {
            $this->createEmptyProtocol();
        }
        foreach ($protocol as $id => $row) {
            $row_correct = true;
            foreach ($row['columns'] as $col_id => $col) {
                $p = &$this->batchprotocol[$id]['columns'][$col_id]['column_protocols'][$ctype];
                $column_index = $this->batchprotocol[$id]['columns'][$col_id]['column_index'];
                $p['left_part'] = $col[$ctype]['left_part'];
                $rule_intro = $col[$ctype]['rule_intro'];
                $right_part = array();
                $rule = '';
                foreach ($col[$ctype]['controlling_cells'] as $ccell ) {
                    $ccell['summation'] == 1 ? $sign = '+' : $sign = '-';
                    if ($ccell['right_part']) {
                        $right_part[] = $sign.(float)$ccell['right_part'];
                    }
                    switch ($ctype) {
                        case 'втк':
                            $rule .= $sign. 'c.'. $ccell['crow_number']. ',г.' . $ccell['ccol_number'] . ' ';
                            break;
                        case 'вфк':
                            $rule .= $sign. 'т.' . $ccell['crow_tablecode'] . ',c.'. $ccell['crow_number']. ',г.' . $ccell['ccol_number'] . ' ';
                            break;
                        case 'мфк':
                            $rule .= $sign. 'ф.' . $ccell['crow_formcode'] . ',т.' . $ccell['crow_tablecode'] . ',c.'. $ccell['crow_number']. ',г.' . $ccell['ccol_number'] . ' ';
                            break;
                        case 'кгр':
                            $rule .= $sign. 'г.' . $ccell['ccol_number'] . ' ';
                            break;
                    }

                    $bool_sign = $ccell['bool_sign'];
                }
                unset($col[$ctype]['controlling_cells']);
                $boolean = ValidateCellByMi::$bool_id[$bool_sign];
                $boolean_readable = ValidateCellByMi::$bool_readable[$boolean];
                $p['right_part'] = implode('', $right_part);
                $expr = "return {$p['right_part']};";
                $right_part_value = eval($expr);
                $p['right_part_value'] = (float)$right_part_value;
                $p['deviation'] = $p['left_part'] - $p['right_part_value'];
                if (!$p['left_part'] && !$p['right_part_value']) {
                    $p['result'] = true;
                }
                else {
                    $bool_expr = "return " . $p['left_part'] .' ' . $boolean . ' ' .$p['right_part_value']. ";";
                    $p['result'] = eval($bool_expr);
                    if ($boolean == 'xor') {
                        $p['result'] = !$p['result'];
                    }
                }
                $p['boolean_sign'] = $boolean;
                $p['boolean_readable'] = $boolean_readable;
                $p['rule'] = $rule_intro . ': г.' . $column_index . ' '. $boolean_readable . ' ( ' . $rule . ')';
                //if (!$p['result']) {
                //  $this->batchprotocol[$id]['columns'][$col_id]['column_correct'] = false;
                //}
                $row_correct = $row_correct && $p['result'];
                $this->batchprotocol[$id]['columns'][$col_id]['column_correct'] = $this->batchprotocol[$id]['columns'][$col_id]['column_correct'] && $p['result'];
            }
            $this->batchprotocol[$id]['row_correct'] = $this->batchprotocol[$id]['row_correct'] && $row_correct;
            if (!$row_correct) {
                $this->table_correct = false;
            }
        }
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
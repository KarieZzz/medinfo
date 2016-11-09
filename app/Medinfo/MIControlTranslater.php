<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 05.08.2016
 * Time: 16:33
 */

namespace App\Medinfo;

use App\Form;
use App\Table;

class MIControlTranslater
{
    public $doc_id;
    public $document;
    public $form;
    public $table;
    public $rows;
    public $columns;
    private $value_cashe;
    public static $control_type = array(1 => 'втк', 2 => 'вфк', 3 => 'мфк', 4 => 'кгр', 5 => 'квс' );
    public static $control_type_readable = array('втк' => 'внутритабличный контроль', 'вфк' => 'внутриформенный контроль', 'мфк' => 'межформенный контроль' );
    public static $boolean_sign = [0 => '^', 1 => '>', 2 => '>=', 3 => '=', 4 => '<=', 5 => '<'];
    public static $boolean_readable = array('^' => 'существует', '>' => 'больше', '>=' => 'больше или равно' , '==' => 'равно', '<=' => 'меньше или равно', '<' => 'меньше');
    public static $number_sign = [1 => '+', -1 => '-'];

    public function __construct($table_id = null)
    {
        if (!$table_id) {
            throw new \Exception("Для выполнения преобразования контроля по таблице необходимо указать Id таблицы");
        }
        $this->table = Table::find($table_id);
        $this->form = Form::find($this->table->form_id);
        $this->rows = \DB::table('rows')
            ->where('table_id', $this->table->id)
            ->where('deleted', 0)
            ->where('medinfo_id', '<>', 0)
            ->orderBy('row_index')->get();
        // Для контроля выбираем только графы со вводом данных
        $this->columns = \DB::table('columns')
            ->where('table_id', $this->table->id)
            ->where('deleted', 0)
            ->where('content_type', 4)
            ->where('medinfo_id', '<>', 0)
            ->orderBy('column_index')->get();
    }

    public function translateAll()
    {
        $protocol['intable'] = $this->InTableRowControl();
        $protocol['inform'] = $this->InFormRowControl();
        $protocol['inreport'] = $this->InReportRowControl();
        $protocol['columns'] = $this->ColumnControl();
        $protocol['inrow'] = $this->InRowControl();
        //$protocol['table_id'] = $this->table->id;
        return $protocol;
    }

    public function InTableRowControl() {
        $ctype_id = 1; // Внутритабличный контроль
        // Итерация по контролируемым строкам с типом контроля "вт.к."
        $rules = $this->getRules($ctype_id);
        if (!$rules) {
            //$protocol['no_rules'] = true;
            return [];
        }
        $i = 0;
        foreach ($rules as $rule) {
            // Итерация по столбцам текущей таблицы и конвертирование правила к каждому из них
            $column_indexes = [];
            foreach ($this->columns as $column) {
                $crows = $this->getControllingRows($ctype_id, $rule->relation, $column->medinfo_id);
                // Итерация по столбцам, включенным в текущее правило контроля ("корреспонденция граф") - при наличии правила
                if (count($crows) > 0) {
                    $prot = $this->getRowProtocol($ctype_id, $rule, $column, $crows);
                    //$protocol[$i][$column->column_index] = $prot;
                    $arg1 = $prot['left_part_formula'];
                    $arg2 = $prot['right_part_formula'];
                    $arg3 = $prot['boolean_sign'];
                    $column_indexes[] = $column->column_index;
                }
            }
            $arg4 = 'группы(*)';
            if (count($column_indexes) == 1) {
                $arg5 = 'графы(' . $column_indexes[0] . ')';
            } else {
                $arg5 = 'графы(' . implode(',', $column_indexes) . ')';
            }
            if ($arg3 == '^') {
                $protocol[] = 'зависимость('. $arg1 . ', ' . $arg2 . ', ' . $arg4 . ', ' . $arg5 .')';
            } else {
                $protocol[] = 'сравнение('. $arg1 . ', ' . $arg2 . ', '  . $arg3 . ', ' . $arg4 . ', ' . $arg5 .')';
            }
            $i++;
        }

        return $protocol;
    }

    public function InFormRowControl()
    {
        $ctype_id = 2; // Внутриформенный контроль
        // Итерация по контролируемым строкам с типом контроля "вф.к."
        $rules = $this->getRules($ctype_id);
        if (!$rules) {
            //$protocol['no_rules'] = true;
            return [];
        }
        $i = 0;
        foreach ($rules as $rule) {
            $column_indexes = [];

            foreach ($this->columns as $column) {
                $crows = $this->getControllingRows($ctype_id, $rule->relation, $column->medinfo_id);
                // Итерация по столбцам, включенным в текущее правило контроля ("корреспонденция граф") - при наличии правила
                if (count($crows) > 0) {
                    $prot = $this->getRowProtocol($ctype_id, $rule, $column, $crows);
                    $arg1 = $prot['left_part_formula'];
                    $arg2 = $prot['right_part_formula'];
                    $arg3 = $prot['boolean_sign'];
                    $arg4 = 'группы(*)';
                    $arg5 = 'графы()';
                    if ($arg3 == '^') {
                        $protocol[] = 'зависимость('. $arg1 . ', ' . $arg2 . ', ' . $arg4 . ', ' . $arg5 .')';
                    } else {
                        $protocol[] = 'сравнение('. $arg1 . ', ' . $arg2 . ', '  . $arg3 . ', ' . $arg4 . ', ' . $arg5 .')';
                    }
                }
            }
            $i++;
        }
        return $protocol;
    }

    public function InReportRowControl()
    {
        $ctype_id = 3; // Межформенный контроль
        // Итерация по контролируемым строкам с типом контроля "вф.к."
        $rules = $this->getRules($ctype_id);
        if (!$rules) {
            //$protocol['no_rules'] = true;
            return [];
        }
        $i = 0;
        foreach ($rules as $rule) {
            // Итерация по столбцам текущей таблицы и выполнение правила к каждому из них
            foreach ($this->columns as $column) {
                $crows = $this->getControllingRows($ctype_id, $rule->relation, $column->medinfo_id);
                // Итерация по столбцам, включенным в текущее правило контроля ("корреспонденция граф") - при наличии правила
                if (count($crows) > 0) {
                    $prot = $this->getRowProtocol($ctype_id, $rule, $column, $crows);
                    $arg1 = $prot['left_part_formula'];
                    $arg2 = $prot['right_part_formula'];
                    $arg3 = $prot['boolean_sign'];
                    $arg4 = 'группы(*)';
                    $arg5 = 'графы()';
                    if ($arg3 == '^') {
                        $protocol[] = 'зависимость('. $arg1 . ', ' . $arg2 . ', ' . $arg4 . ', ' . $arg5 .')';
                    } else {
                        $protocol[] = 'сравнение('. $arg1 . ', ' . $arg2 . ', '  . $arg3 . ', ' . $arg4 . ', ' . $arg5 .')';
                    }
                }
            }
            $i++;
        }
        return $protocol;
    }

    public function ColumnControl()
    {
        $rule_type = 4;
        $q_rule = "SELECT relation FROM controlled_rows WHERE table_id = {$this->table->id} AND control_scope = $rule_type";
        //dd($q_rule);
        $rule = \DB::selectOne($q_rule);
        if (!$rule) {
            //$protocol['no_rules'] = true;
            return [];
        }
        $q_columns_range = "SELECT first_col, first_col+count_col last_col FROM controlling_rows WHERE rec_id = {$rule->relation}";
        //echo $q_columns_range;
        $columns_range = \DB::selectOne($q_columns_range);
        $q_columns = "SELECT ccols.controlled, columns.id controlling, columns.column_index, ccols.boolean_sign, ccols.number_sign FROM controlled_columns ccols
          JOIN columns ON columns.medinfo_id = ccols.controlling AND columns.table_id = {$this->table->id}
          WHERE ccols.rec_id >= {$columns_range->first_col} AND ccols.rec_id < {$columns_range->last_col}";
        $ccols = collect(\DB::select($q_columns));
        foreach ($this->columns as $column) {
            $controlled = $ccols->where('controlled', $column->medinfo_id);
            if (count($controlled) > 0) {
                $column_id = $column->id;
                $left_part_formula = 'ФТСГ' . $column->column_index;
                $right_part_formula = '';
                foreach ($controlled  as $ccell) {
                    $boolean_sign = self::$boolean_sign[$ccell->boolean_sign];
                    $right_part_formula .= self::$number_sign[$ccell->number_sign] . 'ФТСГ' . $ccell->column_index;
                }
                if ($boolean_sign == '^') {
                    $formula = 'зависимость(' . $left_part_formula . ', ' . $right_part_formula . ', группы(*), строки(*)'  . ')';
                } else {
                    $formula = 'сравнение(' . $left_part_formula . ', ' . $right_part_formula . ', ' . $boolean_sign . ', группы(*), строки(*)'  . ')';
                }
                $protocol[] = $formula;
            }
        }
        return $protocol;
    }

    public function inRowControl()
    {
        $rule_type = 5;
        $rules = $this->getRules($rule_type);
        if (!$rules) {
            //$protocol['no_rules'] = true;
            return [];
        }
        $i = 0;
        foreach ($rules as $rule) {
            $q_columns_range = "SELECT first_col, first_col+count_col last_col FROM controlling_rows WHERE rec_id = {$rule->relation}";
            $columns_range = \DB::selectOne($q_columns_range);
            $q_columns = "SELECT ccols.controlled, columns.id controlling, columns.column_index, ccols.boolean_sign, ccols.number_sign FROM controlled_columns ccols
              JOIN columns ON columns.medinfo_id = ccols.controlling AND columns.table_id = {$this->table->id}
              WHERE ccols.rec_id >= {$columns_range->first_col} AND ccols.rec_id < {$columns_range->last_col}";
            $ccols = collect(\DB::select($q_columns));

            foreach ($this->columns as $column) {
                $controlled = $ccols->where('controlled', $column->medinfo_id);
                if (count($controlled) > 0) {
                    $column_id = $column->id;
                    $left_part_formula = 'ФТС' . $rule->row_code . 'Г' . $column->column_index;
                    $right_part_formula = '';
                    foreach ($controlled  as $ccell) {
                        $boolean_sign = self::$boolean_sign[$ccell->boolean_sign];
                        $right_part_formula .= self::$number_sign[$ccell->number_sign] . 'ФТС' . $rule->row_code . 'Г' . $ccell->column_index;
                    }
                    if ($boolean_sign == '^') {
                        $formula = 'зависимость(' . $left_part_formula . ', ' . $right_part_formula . ', группы(*), строки()'  . ')';
                    } else {
                        $formula = 'сравнение(' . $left_part_formula . ', ' . $right_part_formula . ', ' . $boolean_sign . ', группы(*), строки()'  . ')';
                    }
                    $protocol[] = $formula;
                }
            }
            $i++;
        }
        return $protocol;
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
        $column_index_lenght = -strlen($column->column_index);
        if ($rule_type == 1 ) {
            $row_protocol['left_part_formula'] = 'ФТС' . $rule->row_code . 'Г';
        } else {
            $row_protocol['left_part_formula'] = 'ФТС' . $rule->row_code . 'Г' . $column->column_index;
        }
        $right_part_formula = '';
        foreach ($crows as $crow) {
            $row_protocol['boolean_sign'] = self::$boolean_sign[$crow->boolean_sign];
            if ($column->column_index == $crow->column_index && $rule_type == 1) {
                $right_part_formula .= substr($this->getRightPart($rule_type, $crow), 0, $column_index_lenght);
            } else {
                $right_part_formula .= $this->getRightPart($rule_type, $crow);
            }

        }
        $row_protocol['right_part_formula'] = $right_part_formula;
        return $row_protocol;
    }

    private function getRightPart($rule_type, $crow)
    {
        switch ($rule_type) {
            case 1:
                $part = self::$number_sign[$crow->number_sign] . 'ФТС' . $crow->row_code . 'Г' . $crow->column_index;
                break;
            case 2:
                $part = self::$number_sign[$crow->number_sign] . 'ФТ' . $crow->table_code . 'С' . $crow->row_code
                    . 'Г' . $crow->column_index;
                break;
            case 3:
                $part = self::$number_sign[$crow->number_sign] . 'Ф' . $crow->form_code . 'Т' . $crow->table_code
                    . 'С' . $crow->row_code . 'Г' . $crow->column_index;
                break;
            default:
                $part = "Неизвестный тип контроля";
                break;
        }
        return $part;
    }

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
        $q_crows = "SELECT r.row_id, columns.id col_id, r.table_id, r.form_id,
                  forms.form_code, tables.table_code, rows.row_code, columns.column_index, columns.decimal_count,
                  c.boolean_sign, c.number_sign
                  FROM controlling_rows r
                  JOIN controlled_columns c ON c.rec_id >= r.first_col AND c.rec_id < r.first_col + r.count_col
                  JOIN forms ON forms.id = r.form_id
                  JOIN tables ON tables.id = r.table_id
                  JOIN rows ON rows.id = r.row_id
                  JOIN columns ON c.controlling = columns.medinfo_id AND columns.table_id = r.table_id
                  WHERE r.relation = {$relation} AND r.form_id $f_sign {$this->form->id} AND r.table_id $t_sign {$this->table->id} AND c.controlled = $column
                  ORDER BY rows.row_index";
        return \DB::select($q_crows);
    }
}
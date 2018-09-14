<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 05.09.2018
 * Time: 11:40
 */

namespace App\Medinfo\Control;

use App\MedstatNskControl;
use App\MedstatNskTableLink;
use App\Form;
use App\Table;
use App\Row;
use App\Column;

class ConvertNskControls
{
    public $forms;
    public $form;
    public $table;
    public $host_table;
    public $table_errors = [];
    public $convert_errors = [];
    public $column_offset;

    public function __construct($selected_form = null)
    {
        if (!$selected_form) {
            //$this->forms = Form::Real()->HasMedstatNSK()->get();
            $this->forms = Form::Real()->HasMedstatNSK()->where('form_code', '30')->get();
            //$this->forms = Form::Real()->HasMedstatNSK()->where('form_code', '14')->get();
        }
    }

    public function covertInTable()
    {
        foreach ($this->forms as $this->form) {
            $table_links = MedstatNskTableLink::OfForm($this->form->medstatnsk_id)->orderBy('tablen')->get();
            foreach ($table_links as $table_link) {
                $this->host_table = Table::OfMedstatNsk($table_link->id)->first();
                if (!$this->table) {
                    $this->table_errors[] = ['form_code' => $this->form->form_code, 'table_code' => $table_link->tablen, 'comment' => 'Таблица отсутствует в системе'];
                } else {
                    $intables = MedstatNskControl::InTable()->NSKForm($this->form->medstatnsk_id)->NSKTableCode($table_link->tablen)->orderBy('left')->get();
                    $table_fixcol = Column::OfTable($this->host_table->id)->Headers()->count();
                    $this->column_offset = $table_link->fixcol - $table_fixcol;
                    $converted = $this->convertInTables($intables);
                }
            }
        }
        dump($this->convert_errors);
        dd($this->table_errors);
        //dd($form);
    }

    public function convertInterTable()
    {
        foreach ($this->forms as $this->form) {
            $intertables = MedstatNskControl::InterTable()->NSKForm($this->form->medstatnsk_id)->orderBy('left')->get();
            $converted = $this->convertInterTables($intertables);
        }
        dump($this->convert_errors);
        dd($this->table_errors);
    }

    public function convertInTables($intables)
    {
        $converted = 0;
        foreach ($intables as $intable) {
            echo '<p>' . $intable->left . ' ' . $intable->relation . ' ' . $intable->right . ' scope:' . $intable->cycle . '</p>';
            $left = $this->initialProcessing($intable->left);
            $right = $this->initialProcessing($intable->right);
            $converted_left = $this->convertPart($left);
            $converted_right = $this->convertPart($right);
            if (empty($intable->cycle)) {
                echo '<p style="color: blue">сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $intable->relation . ')</p>';
            } else {
                $converted_iteration = $this->convertIteration($intable->cycle);
                echo '<p style="color: blue">сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $intable->relation . ' , ,' . $converted_iteration . ')</p>';
            }

            $converted++;
        }
        return $converted;
    }

    public function convertInterTables($intertables)
    {
        $converted = 0;
        foreach ($intertables as $intertable) {
            $source_formula = $intertable->left . ' ' . $intertable->relation . ' ' . $intertable->right . ' scope:' . $intertable->cycle;
            echo '<p>' . $source_formula . '</p>';
            $left = $this->initialProcessing($intertable->left);
            $right = $this->initialProcessing($intertable->right);
            $lefmost_table_found = preg_match('/Т\([0-9\.]*(\d{4})\)/u', $left, $lefmost_table);
            if ($lefmost_table_found) {
                $this->host_table = Table::OfFormTableCode($this->form->id, $lefmost_table[1])->first();
                if (!$this->host_table) {
                    $this->table_errors[] = ['form_code' => $this->form->form_code, 'table_code' => $lefmost_table[1], 'comment' => 'Таблица отсутствует в системе'];
                    continue;
                }
            } else {
                $this->convert_errors[] = ['element' => '',
                    'formula' => $source_formula,
                    'form_code' => $this->form->form_code,
                    'table_code' => 'В формуле межтабличного контроля не найдена ссылка на таблицу'];
                continue;
            }
            $converted_left = $this->convertIntabPart($left);
            $converted_right = $this->convertIntabPart($right);

            //$right = 'Т(3.2100)ГР[3,9]СТР[65..69]-Т(3.2100)ГР[5,12]СТР[65..69]';
            //dump($right);
            //$right = $this->initialProcessing($right);
            //$converted_right = $this->convertIntabPart($right);
            //dd($converted_right);

            if (empty($intertable->cycle)) {
                echo '<p style="color: blue">сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $intertable->relation . ')</p>';
            } else {
                $converted_iteration = $this->convertIteration($intertable->cycle);
                echo '<p style="color: blue">сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $intertable->relation . ' , ,' . $converted_iteration . ')</p>';
            }

            $converted++;
        }
        return $converted;
    }

    public function convertIntabPart($part)
    {
        //$part = 'Т(3.2100)ГР[3,9]СТР[65..69]-Т(3.2100)ГР[5,12]СТР[65..69]';
        //преобразование ссылок на таблицу типа Т(3.2100) -> Т2100
        $part = preg_replace('/Т\([0-9\.]*(\d{4})\)/u', 'Т\1', $part);
        $table_found = preg_match_all('/Т(\d{4})/u', $part, $table_codes);
        if (!$table_found) {
            $this->convert_errors[] = ['element' => '',
                'formula' => $part,
                'form_code' => $this->form->form_code,
                'table_code' => 'В части формулы межтабличного контроля не найдены ссылки на таблицы'];;
        }
        $prefixes = array_map(function ($t) {
            $ret = '';
            if ($t !== $this->host_table->table_code) {
                $ret = $t;
            }
            return $ret;
        }, $table_codes[1]);

        //dump($part);
        //dump($table_codes);
        //dump($prefixes);

        return $this->convertPart($part, $prefixes);
    }

    public function initialProcessing($part)
    {
        $part = mb_strtoupper($part);
        $rc_patterns = ['/(СТР)/u', '/(ГР)/u'];
        $rc_replacements = ['С', 'Г'];
        return preg_replace($rc_patterns, $rc_replacements, $part);
    }

    public function convertPart($part, $prefixes = null)
    {
        $elements = preg_split('/[\+\-!\(!\)]/', $part, -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($prefixes) && count($prefixes) <> count($elements)) {
            $this->convert_errors[] = ['element' => '', 'formula' => $part, 'form_code' => $this->form->form_code, 'table_code' => $this->table->table_code];
            return 'Ошибка конвертирования: несоответствие числа префиксов (Ф и Т) и числа элементов в части формулы сравнения';
        } elseif (!$prefixes) {
            $prefixes = array_pad([], count($elements), '');
        }
        /*        $elements = [];
                $element = '';
                for ($i = 0 ; $i < mb_strlen($part); $i++) {
                    $c = mb_substr($part, $i, 1);
                    if ($c === '+' || $c === '-'  ) {
                        $elements[] = $element;
                        $element = '';
                    } else {
                        $element .= $c;
                    }
                    if ($i == mb_strlen($part)-1) {
                        $elements[] = $element;
                    }
                }*/
        $part_replacements = [];
        $i = 0;
        //dump($elements);
        foreach ($elements as $element) {
            if (!$this->setCurrentTable($prefixes[$i])) {
                $prefixes[$i] = "Ошибка конвертирования кода таблицы ({$prefixes[$i]})";
            }
            //dump($element);
            $element_replacements = [];
            $col_summ_elements = [];
            $row_key = $this->setRowKey($element, $element_replacements, $part);
            $row_summ_key = $this->setRowSummKey($element, $element_replacements, $part);
            $column_key = $this->setColumnKey($element, $element_replacements, $part);
            $column_summ_key = $this->setColumnSummKey($element, $element_replacements, $col_summ_elements, $part);
            //dump($row_key, $row_summ_key, $column_key, $column_summ_key);

            //$part_replacements[] = $this->setPartReplacements();
            empty($prefixes[$i]) ? $tlink = '' : $tlink = 'Т' . $prefixes[$i];
            if (count($element_replacements) === 1) {
                $part_replacements[] = $tlink . $element_replacements[0];
            } elseif (count($element_replacements) === 2) {
                switch (true) {
                    case (!$row_summ_key && !$column_summ_key) :
                        $part_replacements[] = $tlink . $element_replacements[0] . $element_replacements[1];
                        break;
                    case ($row_summ_key && $column_key) :
                        $part_replacements[] = preg_replace('/С[0-9.\-]+/u', $tlink . '\0' . $element_replacements[1], $element_replacements[0]);
                        break;
                    case ($column_summ_key && $row_key) :
                        $part_replacements[] = preg_replace('/Г[0-9.\-]+/u', $tlink . $element_replacements[0] . '\0', $element_replacements[1]);
                        break;
                    case ($row_summ_key && $column_summ_key) :
                        //dump($element_replacements);
                        $rc_replacements = [];
                        echo '<span style="color: red">Одновременная итерация и по строкам и по графам ' . $element . ' </span>';
                        for ($j = 0; $j < count($col_summ_elements); $j++) {
                            $rc_replacements[$j] = preg_replace('/С[0-9.\-]+/u', $tlink . '\0' . $col_summ_elements[$j], $element_replacements[0]);
                        }
                        //dump($rc_replacements);
                        $part_replacements[] = '(' . implode('+', $rc_replacements) . ')';
                        break;
                }
            }
            $i++;
            //dump($element);
            //dump($prefixes);
            //dump($element_replacements);
            //dump($part_replacements);
        }
        if (count($part_replacements) > 0) {
            $converted = str_replace($elements, $part_replacements, $part);
        } else {
            $converted = $part;
        }
        //if ($part == 'С82Г[9,11]-Г[9,11]С[83..90,92]') {
        //dd($replaced);
        //}

        //dump($convert_errors);
        return $converted;
    }

    public function convertIteration($cycle)
    {
        $cycle = mb_strtoupper($cycle);
        $converted = '';
        $patterns = array('/СТР/', '/ГР/', '/\[/', '/\]/', '/\.\./');
        $repalcements = array('строки', 'графы', '(', ')', '-');
        $converted = preg_replace($patterns, $repalcements, $cycle);
        preg_match_all('/\d+/u', $converted, $indexes);
        $reversed_indexes = array_reverse(array_map(function ($l) {
            return '/' . $l . '/';
        }, $indexes[0]));
        $index_replacements = [];
        if (mb_substr($converted, 0, 1) === 'с') {
            foreach ($indexes[0] as $index) {
                $index_replacements[] = $this->setRowLink($index, $converted, 'ошибка ссылки в итерации по строкам');
            }
            $reversed_index_replacements = array_reverse($index_replacements);
            //dump($converted);
            //dump($reversed_indexes);
            //dump($reversed_index_replacements);
            $converted = preg_replace($reversed_indexes, $reversed_index_replacements, $converted, 1);
        } elseif (mb_substr($converted, 0, 1) === 'г') {
            foreach ($indexes[0] as $index) {
                $index_replacements[] = $this->setColumnLink($index, $converted, 'ошибка ссылки в итерации по графам');
            }
            $reversed_index_replacements = array_reverse($index_replacements);
            $converted = preg_replace($reversed_indexes, $reversed_index_replacements, $converted);
        }
        return $converted;
    }

    public function setCurrentTable($code)
    {
        if (empty($code)) {
            $this->table = $this->host_table;
        } else {
            $this->table = Table::OfFormTableCode($this->form->id, $code)->first();
        }
        if (!$this->table) {
            $this->table_errors[] = ['form_code' => $this->form->form_code, 'table_code' => $code, 'comment' => 'Таблица отсутствует в системе'];
            return false;
        } else {
            $table_fixcol = Column::OfTable($this->table->id)->Headers()->count();
            $table_link = MedstatNskTableLink::find($this->table->medstatnsk_id);
            $this->column_offset = $table_link->fixcol - $table_fixcol;
        }
        //dump($this->table);
        return true;
    }

    public function setRowKey($element, &$element_replacements, $part)
    {
        $row_key = false;
        if (preg_match('/С(\d+)/u', $element, $row_simple)) {
            $row_key = true;
            $element_replacements[] = 'С' . $this->setRowLink($row_simple[1], $element, $part);
        }
        return $row_key;
    }

    public function setRowSummKey($element, &$element_replacements, $part)
    {
        $row_summ_key = false;
        if (preg_match('/С\[([0-9.,]+)\]/u', $element, $row_summ)) {
            $row_summ_elements = explode(',', $row_summ[1]);
            foreach ($row_summ_elements as &$summ_element) {
                if (strstr($summ_element, '..')) {
                    $diapazon = explode('..', $summ_element);
                    $row1 = $this->setRowLink($diapazon[0], $element, $part);
                    $row2 = $this->setRowLink($diapazon[1], $element, $part);
                    $summ_element = 'С' . $row1 . ':С' . $row2;
                } else {
                    $summ_element = 'С' . $this->setRowLink($summ_element, $element, $part);;
                }
            }
            $row_summ_key = true;
            $element_replacements[] = 'сумма(' . implode(',', $row_summ_elements) . ')';
        }
        return $row_summ_key;
    }

    public function setColumnKey($element, &$element_replacements, $part)
    {
        $column_key = false;
        if (preg_match('/Г(\d+)/u', $element, $column_simple)) {
            $column_key = true;
            $element_replacements[] = 'Г' . $this->setColumnLink($column_simple[1], $element, $part);
        }
        return $column_key;
    }

    public function setColumnSummKey($element, &$element_replacements, &$col_summ_elements, $part)
    {
        $column_summ_key = false;
        if (preg_match('/Г\[([0-9.,]+)\]/u', $element, $column_summ)) {
            $col_summ_elements = explode(',', $column_summ[1]);
            foreach ($col_summ_elements as &$colsumm_element) {
                if (strstr($colsumm_element, '..')) {
                    $col_diapazon = explode('..', $colsumm_element);
                    $col1 = $this->setColumnLink($col_diapazon[0], $element, $part);
                    $col2 = $this->setColumnLink($col_diapazon[1], $element, $part);
                    $colsumm_element = 'Г' . $col1 . ':Г' . $col2;
                } else {
                    $colsumm_element = 'Г' . $this->setColumnLink($colsumm_element, $element, $part);
                }
            }
            $column_summ_key = true;
            $element_replacements[] = 'сумма(' . implode(',', $col_summ_elements) . ')';
        }
        return $column_summ_key;
    }

    public function setPartReplacements()
    {

    }

    public function setRowLink($index, $element = null, $part = null)
    {
        $row = Row::OfTableRowIndex($this->table->id, $index)->first();
        if (!$row) {
            $this->convert_errors[] = ['element' => $element, 'formula' => $part, 'form_code' => $this->form->form_code, 'table_code' => $this->table->table_code];
            return $element . '(Ошибка конвертирования: строка не найдена)';
        } else {
            return $row->row_code;
        }
    }

    public function setColumnLink($index, $element, $part)
    {
        $column = Column::OfTableColumnIndex($this->table->id, $index - $this->column_offset)->first();
        if (!$column) {
            $convert_errors[] = ['element' => $element, 'formula' => $part, 'form_code' => $this->form->form_code, 'table_code' => $this->table->table_code, 'offset' => $this->column_offset];
            return $element . '(Ошибка конвертирования: графа не найдена)';
        } else {
            return $column->column_code;
        }
    }

}
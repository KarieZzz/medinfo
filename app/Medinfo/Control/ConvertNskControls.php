<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 05.09.2018
 * Time: 11:40
 */

namespace App\Medinfo\Control;

use App\MedstatNskControl;
use App\MedstatNskFormLink;
use App\MedstatNskTableLink;
use App\Form;
use App\Table;
use App\Row;
use App\Column;
use Carbon\Carbon;

class ConvertNskControls
{
    public $forms;
    public $form;
    public $host_form;
    public $table;
    public $host_table;
    public $table_errors = [];
    public $convert_errors = [];
    public $column_offset;
    public $datetime;

    public function __construct(array $selected_forms)
    {
        $this->forms = Form::Real()->HasMedstatNSK()->whereIn('id', $selected_forms)->get();
        $this->datetime = Carbon::now();
    }

    public function covertInTableControls()
    {
        $this->convert_errors = [];
        $this->table_errors = [];
        $converted = [];
        $i = 0;
        foreach ($this->forms as $this->form) {
            $converted['forms'][$i] = [ 'form_id' => $this->form->id, 'form_code' =>  $this->form->form_code ];
            $table_links = MedstatNskTableLink::OfForm($this->form->medstatnsk_id)->orderBy('tablen')->get();
            $j = 0;
            foreach ($table_links as $table_link) {
                $this->host_form = $this->form;
                $this->host_table = Table::OfMedstatNsk($table_link->id)->first();
                if (!$this->host_table) {
                    $this->table_errors[] = ['form_code' => $this->form->form_code, 'table_code' => $table_link->tablen, 'comment' => 'Таблица отсутствует в системе'];
                } else {
                    $intables = MedstatNskControl::InTable()->NSKForm($this->form->medstatnsk_id)->NSKTableCode($table_link->tablen)->orderBy('left')->get();
                    $table_fixcol = Column::OfTable($this->host_table->id)->Headers()->count();
                    $this->column_offset = $table_link->fixcol - $table_fixcol;
                    $converted['forms'][$i]['tables'][$j]['table'] = ['table_id' => $this->host_table->id, 'table_code' => $this->host_table->table_code];
                    $converted['forms'][$i]['tables'][$j]['scripts'] = $this->convertInTables($intables);
                    $j++;
                }
            }
            $i++;
        }
        $converted['convert_errors'] = $this->convert_errors;
        $converted['table_errors'] = $this->table_errors;
        return $converted;

    }

    public function convertInterTableControls()
    {
        $this->convert_errors = [];
        $this->table_errors = [];
        $converted = [];
        $i = 0;
        foreach ($this->forms as $this->form) {
            $converted['forms'][$i] = [ 'form_id' => $this->form->id, 'form_code' =>  $this->form->form_code ];
            $intertables = MedstatNskControl::InterTable()->NSKForm($this->form->medstatnsk_id)->orderBy('left')->get();
            $this->host_form = $this->form;
            $converted['forms'][$i]['tables'] = $this->convertInterTables($intertables);
            $i++;
        }
        $converted['convert_errors'] = $this->convert_errors;
        $converted['table_errors'] = $this->table_errors;
        return $converted;
    }

    public function convertInterFormControls()
    {
        // При выборе межформенных контролей игнорируем межсрезовые контроли
        $interforms = MedstatNskControl::InterForm()->where('cycle', '')->orderBy('left')->get();
        $this->convert_errors = [];
        $this->table_errors = [];
        $converted = [];
        $i = 0;
        foreach ($interforms as $interform) {
            $source_formula = $interform->left . ' ' . $interform->relation . ' ' . $interform->right ;
            $left = $this->initialProcessing($interform->left);
            $right = $this->initialProcessing($interform->right);
            $lefmost_ft_link_found = preg_match('/Ф([а-яА-СУ-Я0-9]+)Т\([0-9\.]*(\d{4})\)/u', $left, $lefmost_link);
            if ($lefmost_ft_link_found) {
                $nsk_form = MedstatNskFormLink::OfCode('Ф'. $lefmost_link[1])->first();
                $this->host_form = $nsk_form->form;
                //dd($this->host_form);
                if (!$this->host_form) {
                    $this->table_errors[] = ['form_code' => $lefmost_link[1], 'table_code' => $lefmost_link[2], 'comment' => 'Форма отсутствует в системе'];
                    continue;
                }
                $this->host_table = Table::OfFormTableCode($this->host_form->id, $lefmost_link[2])->first();
                if (!$this->host_table) {
                    $this->table_errors[] = ['form_code' => $this->host_form->form_code, 'table_code' => $lefmost_link[2], 'comment' => 'Таблица отсутствует в системе'];
                    continue;
                }
                $converted[$i]['form'] = [ 'form_id' => $this->host_form->id, 'form_code' =>  $this->host_form->form_code ];
                $converted[$i]['table'] = ['table_id' => $this->host_table->id, 'table_code' => $this->host_table->table_code];
            } else {
                $this->convert_errors[] = ['element' => '',
                    'formula' => $source_formula,
                    'form_code' => $this->form->form_code,
                    'table_code' => 'В формуле межформенного контроля не найдена ссылка на форму/таблицу'];
                continue;
            }

            $converted_left = $this->convertInterFormPart($left);
            $converted_right = $this->convertInterFormPart($right);
            $converted[$i]['source_script'] = $source_formula;
            $converted[$i]['converted_script'] = 'сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $interform->relation . ')';
            $i++;
        }
        $converted['convert_errors'] = $this->convert_errors;
        $converted['table_errors'] = $this->table_errors;
        return $converted;
    }

    public function convertInTables($intables)
    {
        $converted = [];
        $count = $intables->count();
        for ($i = 0; $i < $count; $i++) {
            $converted[$i]['source_script'] = $intables[$i]->left . ' ' . $intables[$i]->relation . ' ' . $intables[$i]->right . ' scope:' . $intables[$i]->cycle ;
            $converted[$i]['comment'] = $intables[$i]->comment . ' (конв. МС(НСК) ' . $this->datetime . ')';
            $left = $this->initialProcessing($intables[$i]->left);
            $right = $this->initialProcessing($intables[$i]->right);
            $converted_left = $this->convertPart($left);
            $converted_right = $this->convertPart($right);
            if (empty($intables[$i]->cycle)) {
                $converted[$i]['converted_script'] = 'сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $intables[$i]->relation . ')';
            } else {
                $converted_iteration = $this->convertIteration($intables[$i]->cycle);
                $converted[$i]['converted_script'] = 'сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $intables[$i]->relation . ' , ,' . $converted_iteration . ')';
            }
        }
        return $converted;
    }

    public function convertInterTables($intertables)
    {
        $converted = [];
        $i = 0;
        foreach ($intertables as $intertable) {
            $source_formula = $intertable->left . ' ' . $intertable->relation . ' ' . $intertable->right . ' scope:' . $intertable->cycle;
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
            $converted_left = $this->convertInterTabPart($left);
            $converted_right = $this->convertInterTabPart($right);

            //$right = 'Т(3.2100)ГР[3,9]СТР[65..69]-Т(3.2100)ГР[5,12]СТР[65..69]';
            //dump($right);
            //$right = $this->initialProcessing($right);
            //$converted_right = $this->convertIntabPart($right);
            //dd($converted_right);
            $converted[$i] = ['table_id' => $this->host_table->id, 'table_code' => $this->host_table->table_code];
            $converted[$i]['scripts']['source_script'] = $source_formula;
            $converted[$i]['scripts']['comment'] =  $intertable->comment;
            if (empty($intertable->cycle)) {
                $converted[$i]['scripts']['converted_script'] = 'сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $intertable->relation . ')';
            } else {
                $converted_iteration = $this->convertIteration($intertable->cycle);
                $converted[$i]['scripts']['converted_script'] = 'сравнение(' . $converted_left . ', ' . $converted_right . ', ' . $intertable->relation . ', ,' . $converted_iteration . ')';
            }
            $i++;

        }
        return $converted;
    }

    public function convertInterTabPart($part, $form_prefixes = null)
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

        return $this->convertPart($part, $prefixes, $form_prefixes);
    }

    public function convertInterFormPart($part)
    {
        $form_table_found = preg_match_all('/Ф([а-яА-СУ-Я0-9]+)Т\([0-9\.]*(\d{4})\)/u', $part, $form_table_codes);
        //dd($form_table_codes);
        if (!$form_table_found) {
            $this->convert_errors[] = ['element' => '',
                'formula' => $part,
                'form_code' => 'При конвертировании межформенного контроля, в нем не найдена корректная ссылка на форму',
                'table_code' => ''];;
        }
        $prefixes = array_map(function ($f) {
            $ret = '';
            $nsk_form = MedstatNskFormLink::OfCode('Ф'. $f)->first();
            if ($nsk_form->form->form_code !== $this->host_form->form_code) {
                $ret = $nsk_form->form->form_code;
            }
            return $ret;
        }, $form_table_codes[1]);
        return $this->convertInterTabPart($part, $prefixes);
    }

    public function initialProcessing($part)
    {
        $part = mb_strtoupper($part);
        $rc_patterns = ['/(СТР)/u', '/(ГР)/u'];
        $rc_replacements = ['С', 'Г'];
        return preg_replace($rc_patterns, $rc_replacements, $part);
    }

    public function convertPart($part, $prefixes = null, $form_prefixes = null)
    {
        $elements = preg_split('/[\+\-!\(!\)]/', $part, -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($prefixes) && count($prefixes) <> count($elements)) {
            $this->convert_errors[] = ['element' => '', 'formula' => $part, 'form_code' => $this->form->form_code, 'table_code' => $this->table->table_code];
            return 'Ошибка конвертирования: несоответствие числа префиксов (Т) и числа элементов в части формулы сравнения';
        } elseif (!$prefixes) {
            $prefixes = array_pad([], count($elements), '');
        }
        if (is_array($form_prefixes) && count($form_prefixes) <> count($elements)) {
            $this->convert_errors[] = ['element' => '', 'formula' => $part, 'form_code' => $this->form->form_code, 'table_code' => $this->table->table_code];
            return 'Ошибка конвертирования: несоответствие числа префиксов (Ф) и числа элементов в части формулы сравнения';
        } elseif (!$form_prefixes) {
            $form_prefixes = array_pad([], count($elements), '');
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
            //dump($element);
            if (!$this->setCurrentForm($form_prefixes[$i])) {
                $form_prefixes[$i] = "Ошибка конвертирования кода формы ({$form_prefixes[$i]})";
            }
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
            empty($prefixes[$i]) ? $tlink = '' : $tlink = 'Т' . $prefixes[$i];
            empty($form_prefixes[$i]) ? $flink = '' : $flink = 'Ф' . $form_prefixes[$i];
            //dump($element_replacements);
            //dd($this->form);
            if (count($element_replacements) === 1) {

                $part_replacements[] = $flink . $tlink . $element_replacements[0];
            } elseif (count($element_replacements) === 2) {

                switch (true) {
                    case (!$row_summ_key && !$column_summ_key) :
                        $part_replacements[] = $flink . $tlink . $element_replacements[0] . $element_replacements[1];
                        break;
                    case ($row_summ_key && $column_key) :
                        $part_replacements[] = preg_replace('/С[0-9.\-]+/u', $flink . $tlink . '\0' . $element_replacements[1], $element_replacements[0]);
                        break;
                    case ($column_summ_key && $row_key) :
                        $part_replacements[] = preg_replace('/Г[0-9.\-]+/u', $flink . $tlink . $element_replacements[0] . '\0', $element_replacements[1]);
                        break;
                    case ($row_summ_key && $column_summ_key) :
                        //dump($element_replacements);
                        $rc_replacements = [];
                        //echo '<span style="color: red">Одновременная итерация и по строкам и по графам ' . $element . ' </span>';
                        for ($j = 0; $j < count($col_summ_elements); $j++) {
                            $rc_replacements[$j] = preg_replace('/С[0-9.\-]+/u', $flink . $tlink . '\0' . $col_summ_elements[$j], $element_replacements[0]);
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
            if ( !$table_link ) {
                $this->table_errors[] = ['form_code' => $this->form->form_code, 'table_code' => $code, 'comment' => 'Таблица отсутствует в Медстат (НСК)'];
                return false;
            }
            $this->column_offset = $table_link->fixcol - $table_fixcol;
        }
        //dump($this->table);
        return true;
    }

    public function setCurrentForm($code)
    {
        //dd($this->host_form);
        if (empty($code)) {
            $this->form = $this->host_form;
        } else {
            $this->form = Form::OfCode($code)->first();
        }
        if (!$this->form) {
            $this->table_errors[] = ['form_code' => $code, 'table_code' => '', 'comment' => 'Форма отсутствует в системе'];
            return false;
        }
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

    public function setRowLink($index, $element = null, $part = null)
    {
        if (!$this->table) {
            return $element . '(Ошибка конвертирования: Не указана текущая таблица для конвертирования кода строки )';
        }
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
        if (!$this->table) {
            return $element . '(Ошибка конвертирования: Не указана текущая таблица для конвертирования кода графы )';
        }
        $column = Column::OfTableColumnIndex($this->table->id, $index - $this->column_offset)->first();
        if (!$column) {
            $convert_errors[] = ['element' => $element, 'formula' => $part, 'form_code' => $this->form->form_code, 'table_code' => $this->table->table_code, 'offset' => $this->column_offset];
            return $element . '(Ошибка конвертирования: графа не найдена)';
        } else {
            return $column->column_code;
        }
    }

}
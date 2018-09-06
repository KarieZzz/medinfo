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

    public static function covertInTable()
    {
        $forms = Form::Real()->HasMedstatNSK()->get();
        $table_errors = [];
        foreach ($forms as $form) {
            //dump($form);
            $table_links = MedstatNskTableLink::OfForm($form->medstatnsk_id)->orderBy('tablen')->get();
            foreach ($table_links as $table_link) {
                $table = Table::OfMedstatNsk($table_link->id)->first();
                //dd($table->columns->diff($table->columns->where('medstatnsk_id', null))->min('medstatnsk_id'));
                if (!$table) {
                    $table_errors[] = ['form_code' => $form->form_code, 'table_code' => $table_link->tablen, 'comment' => 'Таблица отсутствует в системе'];
                } else {
                    $intables = MedstatNskControl::InTable()->NSKForm($form->medstatnsk_id)->NSKTableCode($table_link->tablen)->orderBy('left')->get();
                    if (!$table->transposed) {
                        $min_nsk_id = $table->columns->diff($table->columns->where('medstatnsk_id', null))->min('medstatnsk_id');
                        $column_offset = (int)$min_nsk_id - ($table_link->fixcol + 1);
                    } else {
                        $column_offset = 0;
                    }
                    $converted = self::convertInTables($intables, $table, $column_offset);
                }
            }
        }
        dd($table_errors);
        //dd($form);
    }

    public static function convertInTables($intables, Table $table, $column_offset)
    {
        $converted = 0;
        foreach ($intables as $intable) {
            $left = mb_strtoupper($intable->left);
            $right = mb_strtoupper($intable->right);
            $rc_patterns = ['/(СТР)/u' , '/(ГР)/u'];
            $rc_replacements = ['С', 'Г'];

            $left = preg_replace($rc_patterns, $rc_replacements, $left);
            $right = preg_replace($rc_patterns, $rc_replacements, $right);
            echo $left . ' ' . $intable->relation .' ' . $right . ' ' . $intable->cycle . '<br>';

            $converted_left = self::convertPart($left, $table, $column_offset);


            /*            $left_converted = preg_replace_callback('/С(\[[0-9.,]*\])/u', function ($matches) {
                            $patterns = ['/\d+/', '/\.\./', '/\[/', '/\]/'];
                            $replacements = ['С\0', ':' , 'сумма(' , ')'];
                            $left_converted = preg_replace($patterns, $replacements, $matches[1]);
                            dump($matches[1]);
                            //return str_replace(['[', ']', ], ['(', ')'], $left_converted);
                            return $left_converted;
                        }, $left_converted);
                        echo $left_converted . '<br>';*/

            $converted++;
        }
        //dd($left);
        return $converted;
    }

    public static function convertPart($part, Table $table, $column_offset)
    {
        $elements = preg_split('/[\+\-]/', $part);
        $element_replacements = [];
        $convert_errors = [];
        foreach ( $elements as $element ) {
            // преобразования простых ссылок на строки
            if (preg_match('/С(\d+)/u', $element, $row_simple)) {
                $row = Row::OfTableRowIndex($table->id, $row_simple[1])->first();
                if (!$row) {
                    $convert_errors[] = ['element' => $element, 'formula' => $part, 'form_code' => $table->form->form_code, 'table_code' => $table->table_code];
                    $element_replacements[] = $element . '(Ошибка конвертирования: строка не найдена)';
                } else {
                    $element_replacements[] = 'С' . $row->row_code;
                }

            }
            if (preg_match('/С\[([0-9.,]{4,})\]/u', $element, $row_summ)) {
                $summ_elements = explode(',', $row_summ[1]);
                foreach ($summ_elements as &$summ_element) {
                    if (strstr($summ_element, '..')) {
                        $diapazon = explode('..', $summ_element);

                    } else {
                        $summ_element = 'С' . $summ_element;
                    }
                }

                //$row = Row::OfTableRowIndex($table_id, $row_summ[1])->first();
                //$element_replacements[] = 'С' . $row->row_code;
                $element_replacements[] = 'сумма(' . $row_summ[1] . ')';
            }
            if (preg_match('/Г(\d+)/u', $element, $column_simple)) {
                $column = Column::OfTableColumnIndex($table->id, ((int)$column_simple[1])- $column_offset )->first();
                if (!$column) {
                    $convert_errors[] = ['element' => $element, 'formula' => $part, 'form_code' => $table->form->form_code, 'table_code' => $table->table_code, 'offset' => $column_offset];
                    $element_replacements[] = $element . '(Ошибка конвертирования: графа не найдена)';
                } else {
                    $element_replacements[] = 'Г' . $column->column_code;
                }
            }

            /*                $element_replacements[] = preg_replace_callback('/(?:(?:Г(?P<col_summ_pre>\[[0-9.,]{4,}\]))|(?:Г(?P<col_simple_pre>\d+)))?(?:(?:С(?P<row_summ>\[[0-9.,]{4,}\]))|(?:С(?P<row_simple>\d+)))?(?:(?:Г(?P<col_summ_after>\[[0-9.,]{4,}\]))|(?:Г(?P<col_simple_after>\d+)))?/u', function ($matches) {
                                dump($matches);
                                $patterns = ['/\d+/', '/\.\./', '/\[/', '/\]/'];
                                $replacements = ['С\0', ':' , 'сумма(' , ')'];
                                $left_converted = preg_replace($patterns, $replacements, $matches[0]);

                                //return str_replace(['[', ']', ], ['(', ')'], $left_converted);
                                return $left_converted;
                            }, $element);*/
        }
        //dump($element_replacements);
        //dump($convert_errors);
    }

}
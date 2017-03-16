<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 16.09.2016
 * Time: 11:34
 */

namespace App\Medinfo;

use App\Unit;
use App\Form;

class ReportMaker
{
    public static function makeReportByLegal(array $indexes, int $level = 1, int $period = 4)
    {
        //$count_of_indexes = count($indexes['content']);
        //$period = 1; // 2015 год
        //$period = 4; // 2016 год
        $states = [ 2, 4, 8, 16, 32 ]; // Документы со всеми статусами
        $dtype = 1; // Только первичные документв
        switch ($level) {
            case 0:
                $units = Unit::primary()->active()->orderBy('unit_code')->get();
                break;
            case 1:
                $units = Unit::legal()->active()->orderBy('unit_code')->get();
                break;
            case 2:
                $units = Unit::upperLevels()->active()->orderBy('unit_code')->get();
                break;
        }
        $report_units = [];
        foreach ($units as $unit) {
            $report_units[$unit->id]['unit_name'] = $unit->unit_name;
            $report_units[$unit->id]['inn'] = $unit->inn;
            $scope = ['top_node' => (string)$unit->id ];
            $i = 0;
            $row_sum = 0;
            foreach ($indexes['content'] as $index => $rule) {
                $report_units[$unit->id][$i] = [];
                $formula =  $rule['value'];
                preg_match_all('/Ф([а-я0-9.-]+)Т([\w.-]+)С([\w.-]+)Г(\d{1,})/u', $formula, $matches, PREG_SET_ORDER);
                //var_dump($matches);
                $v = 0;
                foreach ($matches as $c_addr) {
                    $form_code = $c_addr[1];
                    $table_code = $c_addr[2];
                    $row_code = $c_addr[3];
                    $col_index = $c_addr[4];
                    $form = Form::where('form_code', $form_code)->first();
                    if ($unit->aggregate) {
                        $periods[] = $period;
                        $scope['forms'] = [ $form->id ];
                        $scope['worker_scope'] = 0;
                        $scope['periods'] = [ $period ];
                        $scope['states'] = $states;
                        $scope['dtypes'] = [ $dtype ];
                        $doc_tree = new DocumentTree($scope);
                        $doc_array = $doc_tree->get_documents();
                        $documents = array();
                        foreach ($doc_array as $doc) {
                            $documents[] = $doc->id;
                        }
                        $strigified_documents = implode(',', $documents);
                        if (empty($strigified_documents)) {
                            $v = 0;
                        } else {
                            $val_q = "SELECT SUM(v.value) AS value FROM statdata v
                                LEFT JOIN documents d ON v.doc_id = d.id
                                JOIN tables t ON v.table_id = t.id
                                LEFT JOIN rows r ON v.row_id = r.id
                                LEFT JOIN columns c ON v.col_id = c.id
                              WHERE d.id in ({$strigified_documents}) AND t.table_code = '$table_code'
                                AND r.row_code = '$row_code' AND c.column_index = $col_index
                              GROUP BY v.table_id, v.row_id, v.col_id";
                            //dd($val_q);
                            $val_res = \DB::selectOne($val_q);
                            $v = $val_res ? $val_res->value :  0;
                        }

                    } else {
                        $val_q = "SELECT v.value AS value FROM statdata v
                          LEFT JOIN documents d on v.doc_id = d.id
                          JOIN tables t on v.table_id = t.id
                          LEFT JOIN rows r on v.row_id = r.id
                          LEFT JOIN columns c on v.col_id = c.id
                        WHERE d.form_id = {$form->id} AND d.ou_id = {$unit->id} AND d.period_id = $period
                          AND t.table_code = '$table_code' AND r.row_code = '$row_code' AND c.column_index = $col_index";
                        $val_res = \DB::selectOne($val_q);
                        $v = $val_res ? $val_res->value :  0;
                    }
                    $formula = str_replace($c_addr[0], $v, $formula);
                }
                $m = new EvalMath;
                $value = 0;
                try {
                    $value = $m->e($formula);
                }
                catch (\Exception $e) {
                    //dd($e);
                }
                //$value = eval('return ' . $formula . ';' );
                //echo $formula . PHP_EOL;
                //echo 'Вычисленное по формуле - ' . $value . PHP_EOL;
                $row_sum += $value;
                $report_units[$unit->id][$i]['id'] = $index;
                $report_units[$unit->id][$i]['value'] = number_format($value, 2, ',',' ');
                $i++;
            }
            //echo $row_sum .PHP_EOL;
            $report_units[$unit->id]['row_sum'] = $row_sum;
        }
        return $report_units;
    }
}
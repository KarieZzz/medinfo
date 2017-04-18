<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 16.09.2016
 * Time: 11:34
 */

namespace App\Medinfo;

use App\Period;
use App\Unit;
use App\Form;
use App\Table;
use App\Document;
use App\Cell;

class ReportMaker
{
    private $period;
    private $states;
    private $dtype;
    private $units;
    private $population_form;
    private $population_rows;
    private $population_column;

    public function __construct(int $level = 1, int $period_id = 4, int $sort_order = 1)
    {
        $this->period = Period::find($period_id);
        $this->states = [ 2, 4, 8, 16, 32 ]; // Документы со всеми статусами
        $this->dtype = 1; // Только первичные документв
        switch ($sort_order) {
            case 1:
                $order = 'territory_type';
                break;
            case 2:
                $order = 'unit_name';
                break;
            case 3:
                $order = 'unit_code';
                break;
        }
        switch ($level) {
            case 0:
                $this->units = Unit::primary()->active()->orderBy($order)->orderBy('unit_name')->get();
                break;
            case 1:
                $this->units = Unit::legal()->active()->orderBy($order)->orderBy('unit_name')->get();
                break;
            case 2:
                $this->units = Unit::territory()->active()->orderBy($order)->orderBy('unit_name')->get();
                // Добавляем в коллекцию "Всего"
                $all = Unit::find(0);
                $this->units->push($all);
                break;
        }
        //dd($this->units);
        $this->population_form = Form::OfCode('100')->first();
        $population_table = Table::OfFormTableCode($this->population_form->id, '1000')->with('rows')->with('columns')->first();
        $this->population_rows = $population_table->rows;
        $this->population_column = $population_table->columns;
        //dd($this->population_column->where('column_index', 3)->first()->id);
    }

    public function makeReportByLegal(array $indexes)
    {
        $report_units = [];
        foreach ($this->units as $unit) {
            $report_units[$unit->id]['unit_name'] = $unit->unit_name;
            //$report_units[$unit->id]['inn'] = $unit->inn;
            $i = 0;
            $row_sum = 0;
            foreach ($indexes['content'] as $index => $rule) {
                $report_units[$unit->id][$i] = [];
                $formula =  $rule['value'];
                $cellcount = preg_match_all('/Ф([а-я0-9.-]+)Т([\w.-]+)С([\w.-]+)Г(\d{1,})/u', $formula, $matches, PREG_SET_ORDER);
                //$cellcount = preg_match_all('/(?:Ф(?P<f>[а-я0-9.-]*))?(?:Т(?P<t>[а-я0-9.-]*))?(?:С(?P<r>[0-9.-]*))?(?:Г(?P<c>\d{1,3}))?(?:П(?P<p>[01]))?/u', $formula, $matches);
                //$cellcount = preg_match_all('/(?:Ф(?P<f>[а-я0-9.-]*))?(?:Т(?P<t>[а-я0-9.-]*))?(?:С(?P<r>[0-9.-]*))?(?:Г(?P<c>\d{1,3}))/u', $formula, $matches, PREG_SET_ORDER);
                //dd($matches);
                $v = 0;
                foreach ($matches as $c_addr) {
                    $form_code = $c_addr[1];
                    $table_code = $c_addr[2];
                    $row_code = $c_addr[3];
                    $col_index = $c_addr[4];
                    $form = Form::where('form_code', $form_code)->first();
                    $v = $this->getAggregatedValue($unit, $form, $table_code, $row_code, $col_index);
                    $formula = str_replace($c_addr[0], $v, $formula);
                }
                $populationlinks = preg_match_all('/население\((\d{1,})\)/u', $formula, $populationmatches, PREG_SET_ORDER);
                foreach ($populationmatches as $populationmatch) {
                    $populationgroup = $populationmatch[1];
                    $population = $this->getPopulation($populationgroup, $unit);
                    $formula = str_replace($populationmatch[0], $population, $formula);
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

    public function getAggregatedValue(Unit $unit, Form $form, $table_code, $row_code, $col_index)
    {
        // Проверка, нужно ли сводить данные по текущему юниту.
        // Если вдруг сводить не нужно, в слюбом случае возвращаем значение для упрощения обработки сводного отчета
        if ($unit->aggregate) {
            $scope = ['top_node' => (string)$unit->id ];
            $scope['forms'] = [ $form->id ];
            $scope['worker_scope'] = 0;
            $scope['periods'] = [ $this->period->id ];
            $scope['states'] = $this->states;
            $scope['dtypes'] = [ $this->dtype ];
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
                        WHERE d.form_id = {$form->id} AND d.ou_id = {$unit->id} AND d.period_id = {$this->period->id}
                          AND t.table_code = '$table_code' AND r.row_code = '$row_code' AND c.column_index = $col_index";
            $val_res = \DB::selectOne($val_q);
            $v = $val_res ? $val_res->value :  0;
        }
        return $v;
    }

    public function getPopulation($population_group = 1, $unit)
    {
        // Если данные группируются по-территориально, то население берем из таблицы 100 соответствующей территории
        // Если id юнита равно нулю, берем все население Иркутской области из выбранной категории
        $population = 0;
        switch (true) {
            case $unit->id === 0 :
            case $unit->node_type == 2 :
                //dd($unit);
                //dd($this->population_form);
                $document = Document::OfTUPF( 2, $unit->id, $this->period->id, $this->population_form->id)->first();
                //$cells = Cell::OfDocument($document->id)->get();

                $cell = Cell::OfDRC($document->id, $this->population_rows->where('row_code', $population_group)->first()->id,  $this->population_column->where('column_index', 3)->first()->id)->first();
                //dd($cell);
                //$population = $cells[$population_group - 1]->value;
                $population = $cell->value;
                break;
            case $unit->node_type == 3 || $unit->node_type == 4 :
                $this->getServicedPopulation();
                break;
            case $unit->node_type == 1 :
                $population = 0;
                break;
        }

        return $population;
    }

    public function getServicedPopulation()
    {
        return 0;
    }

}
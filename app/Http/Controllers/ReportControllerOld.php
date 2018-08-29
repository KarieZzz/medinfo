<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Medinfo\ReportMaker;
use App\ReportPattern;

class ReportControllerOld extends Controller
{
    //
    public $rep_struct =
<<<JSON
{
    "header": {
        "title": "Анализ 30 формы, т. 1001, ФАПы и ФП"
    },
    "content": {
		"index1": {
            "title": "ФАПы",
            "value": "(Ф30Т1001С122Г4+Ф30Т1001С123Г4)*1000/население(1)"
        }
    }
}
JSON;

    // Выбор и расчет показателей для отчета
    public function consolidateIndexes($level, $period)
    {
        $structure = json_decode($this->rep_struct, true);
        $count_of_indexes = count($structure['content']);
        $title = $structure['header']['title'];
        //$indexes = ReportMaker::makeReportByLegal($structure, $level, $period);
        $rp = new ReportMaker($level, $period);
        $result = $rp->makeReportByLegal($structure);
        $indexes = $result[0];
        return view('reports.report', compact('indexes', 'title', 'structure', 'count_of_indexes'));
    }

    public function performReport(ReportPattern $pattern, $period, $sortorder)
    {
        $structure = json_decode($pattern->pattern, true);
        $count_of_indexes = count($structure['content']);
        $title = $structure['header']['title'];
        //$indexes = ReportMaker::makeReportByLegal($structure, $level, $period);
        $rp = new ReportMaker($sortorder, $period, $sortorder);
        $resusult = $rp->makeReportByLegal($structure);
        $indexes = $resusult[0];
        $calculation_errors = $resusult[1];
        return view('reports.report', compact('indexes', 'title', 'structure', 'count_of_indexes', 'calculation_errors'));
    }

    public function getProgess() {
        $id = \Session::getId();
        $manadged = \Session::get('report_progress');
        $current_unit = trim(\Session::get('current_unit'));
        $all = \Session::get('count_of_units');
        $progress = round($manadged/$all*100, 1);
        return ['session_id' => $id, 'manadged' => $manadged, 'current_unit' => $current_unit, 'count_of_units' => $all, 'progress' => $progress ] ;
   }

}

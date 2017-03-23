<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Medinfo\ReportMaker;

class ReportController extends Controller
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
            "value": "Ф30Т1001С122Г4+Ф30Т1001С123Г4"
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
        $indexes = ReportMaker::makeReportByLegal($structure, $level, $period);
        return view('reports.report', compact('indexes', 'title', 'structure', 'count_of_indexes'));
    }

}

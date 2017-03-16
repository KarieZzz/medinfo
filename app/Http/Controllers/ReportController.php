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
        "title": "Анализ 13 формы за 2016 год"
    },
    "content": {
		"index1": {
            "title": "Абортов, всего",
            "value": "Ф13Т1000С1Г4+Ф13Т2000С1Г4"
        },
		"index2": {
            "title": "Абортов по медицинским показаниям",
            "value": "Ф13Т1000С6Г4"
        },
		"index3": {
            "title": "Население",
            "value": "Ф100Т1000С8Г3"
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

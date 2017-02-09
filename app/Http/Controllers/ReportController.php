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
        "title": "Анализ 30 формы, Сопоставление кол-ва участков со штатами"
    },
    "content": {
		"index1": {
            "title": "Терапевты",
            "value": "Ф30Т1100С97Г3"
        },
		"index2": {
            "title": "Терапевтические участки",
            "value": "Ф30Т1107С1Г3"
        },
		"index3": {
            "title": "Педиатры",
            "value": "Ф30Т1100С46Г3"
        },
		"index4": {
            "title": "Педиатрические участки",
            "value": "Ф30Т1107С5Г3"
        },
		"index5": {
            "title": "ВОП",
            "value": "Ф30Т1100С35Г3"
        },
		"index6": {
            "title": "Участки ВОП",
            "value": "Ф30Т1107С4Г3"
        }
    }
}
JSON;

    // Выбор и расчет показателей для отчета
    public function consolidateIndexes()
    {
        $structure = json_decode($this->rep_struct, true);
        $count_of_indexes = count($structure['content']);
        $title = $structure['header']['title'];
        $indexes = ReportMaker::makeReport($structure);
        return view('reports.report', compact('indexes', 'title', 'structure', 'count_of_indexes'));
    }

}

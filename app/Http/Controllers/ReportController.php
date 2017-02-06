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
        "title": "Анализ 30 формы, т. 1001, ФАПы и ФП, т. 8000 кол-во соответствущих построек"
    },
    "content": {
		"index1": {
            "title": "ФАПы",
            "value": "Ф30Т1001С122Г4"
        },
		"index2": {
            "title": "ФП",
            "value": "Ф30Т1001С123Г4"
        },
		"index3": {
            "title": "ФАПы, постройки",
            "value": "Ф30Т8000С5Г3"
        },
		"index4": {
            "title": "ФП, постройки",
            "value": "Ф30Т8000С6Г3"
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

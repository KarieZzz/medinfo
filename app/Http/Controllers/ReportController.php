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
        "title": "Анализ 30 формы, т. 3100, Деятельность круглосуточного стационара"
    },
    "content": {
		"index1": {
            "title": "Среднегодовые койки, всего",
            "value": "Ф30Т3100С1Г5"
        },
		"index2": {
            "title": "Число койкодней, всего",
            "value": "Ф30Т3100С1Г15"
        },
		"index3": {
            "title": "Работа койки по всем профилям",
            "value": "Ф30Т3100С1Г15/Ф30Т3100С1Г5"
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

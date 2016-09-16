<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Medinfo\ReportMaker;

class ReportController extends Controller
{
    //
    public $rep_struct = <<<JSON
{
    "header":       { "title": "Данные 30 формы в разрезе медицинских организаций по кадрам"   },
    "content": {
		"index1":   { "title": "врачи: штатные",          "value": "Ф30Т1100С1Г3"     },
  		"index2":   { "title": "врачи: занятые",          "value": "Ф30Т1100С1Г4"     },
  		"index3":   { "title": "врачи: физлица",          "value": "Ф30Т1100С1Г9"     },
        "index4":   { "title": "средние: штатные",        "value": "Ф30Т1100С139Г3"   },
  		"index5":   { "title": "средние: занятые",        "value": "Ф30Т1100С139Г4"   },
  		"index6":   { "title": "средние: физлица",        "value": "Ф30Т1100С139Г9"   }
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

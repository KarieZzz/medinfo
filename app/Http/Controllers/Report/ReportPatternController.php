<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\ReportPattern;
use App\Period;

class ReportPatternController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('analytics');
    }

    public function index()
    {
        $patterns = ReportPattern::orderBy('name')->get(['id', 'name']);
        $periods = Period::orderBy('name')->get();
        $last_year = Period::LastYear()->first();
        \Session::put('report_progress', 0);
        \Session::save();
        //dd($patterns);
        return view('reports.reportpatterns', compact('patterns', 'periods', 'last_year'));
    }

    public function showIndexes(ReportPattern $pattern)
    {
        // Показываем перечень показатель при выборе паттерна в таблице
        $p = json_decode($pattern->pattern, true);
        $indexes = $p['content'];
        return $indexes;
    }

}

<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('analytics');
    }

    public function compose_query()
    {
        $forms = \App\Form::orderBy('form_index')->get(['id', 'form_code']);
        $periods = \App\Period::orderBy('name')->get();
        $last_year = \App\Period::LastYear()->first();
        $upper_levels = \App\UnitsView::whereIn('type', [1,2,5])->get();
        return view('reports.composequickquery', compact('forms', 'upper_levels', 'periods', 'last_year'));
    }

    public function performReport()
    {
        $patterns = \App\ReportPattern::orderBy('name')->get(['id', 'name']);
        $periods = \App\Period::orderBy('name')->get();
        $last_year = \App\Period::LastYear()->first();
        //dd($patterns);
        return view('reports.analytic_report_by_patterns', compact('patterns', 'periods', 'last_year'));
    }


}

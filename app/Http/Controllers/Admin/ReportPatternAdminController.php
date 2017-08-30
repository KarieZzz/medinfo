<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ReportPatternAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function create()
    {
        return view('reports.composereportpattern');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'report_name' => 'required|max:256',
                'title.*' => 'required',
                'value.*' => 'required',
            ]
        );
        $pattern = new ReportPattern();
        $pattern->name = $request->report_name;
        $newpattern = [];
        $newpattern['header']['title'] = $request->report_name;
        $titles = $request->title;
        $values = $request->value;
        for ($i = 0 ; count($titles) > $i; $i++ ) {
            $newpattern['content']['index'. ($i+1)]['title'] = $titles[$i];
            $newpattern['content']['index'. ($i+1)]['value'] = $values[$i];
        }
        $pattern->pattern = json_encode($newpattern);
        $pattern->save();
        return redirect('/reports/patterns');
    }

    public function edit($id)
    {
        $pattern = ReportPattern::find($id);
        //echo $pattern->pattern;
        $decoded = json_decode($pattern->pattern, true);
        $name = $decoded['header']['title'];
        $indexes = $decoded['content'];

        //dd($indexes);
        return view('reports.updatereportpattern', compact('id', 'name', 'indexes'));
    }

    public function update(Request $request, ReportPattern $pattern )
    {
        //dd($request->all());
        $this->validate($request, [
                'report_name' => 'required|max:256',
                'title.*' => 'required',
                'value.*' => 'required',
            ]
        );
        $pattern->name = $request->report_name;
        $updated_pattern = [];
        $updated_pattern['header']['title'] = $request->report_name;
        $titles = $request->title;
        $values = $request->value;
        for ($i = 0 ; count($titles) > $i; $i++ ) {
            $updated_pattern['content']['index'. ($i+1)]['title'] = $titles[$i];
            $updated_pattern['content']['index'. ($i+1)]['value'] = $values[$i];
        }
        $pattern->pattern = json_encode($updated_pattern);
        $pattern->save();
        //return $updated_pattern;

        //dd(json_encode($updated_pattern));
        return back()->with('status' , 'Схема отчета сохранена');
    }

    public function destroy($id)
    {

    }
}

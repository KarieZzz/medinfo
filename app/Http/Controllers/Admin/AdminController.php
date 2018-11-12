<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $now = Carbon::now();
        $yesterday = Carbon::yesterday();
        $week = $now->subWeek(1);
        $lastDayAccess = \App\AccessLog::Where('occured_at', '>', $yesterday)->count();
        $lastWeekAccess = \App\AccessLog::Where('occured_at', '>', $week)->count();
        $lastDayCellEditing = \App\ValuechangingLog::Where('occured_at', '>', $yesterday)->count();
        $lastWeekCellEditing = \App\ValuechangingLog::Where('occured_at', '>', $week)->count();
        $lastDayStateChanging = \App\StatechangingLog::Where('occured_at', '>', $yesterday)->count();
        $lastWeekStateChanging = \App\StatechangingLog::Where('occured_at', '>', $week)->count();
        //dd($lastDayAccess);
        return view('jqxadmin.home', compact('lastDayAccess', 'lastWeekAccess',
        'lastDayCellEditing', 'lastWeekCellEditing' , 'lastDayStateChanging', 'lastWeekStateChanging'
        ));
    }
}

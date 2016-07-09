<?php

namespace App\Http\Controllers;

use App\AccessLog;
//use Illuminate\Http\Request;
use App\Http\Requests;
//use DB;

class AccessLogController extends Controller
{
    public function index()
    {

        $access_events = AccessLog::all();
        //$access_events = DB::table('access_log')->get();
        return view('logs.accesslog', compact('access_events'));
        //return view('logs.accesslog')->with( $access_events);
    }

    public function  show(AccessLog $event)
    {
        return view('logs.accessevent',  compact('event') );
    }
}

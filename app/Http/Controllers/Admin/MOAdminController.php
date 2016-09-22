<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Unit;
use App\DicUnitType;

class MOAdminController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $unit_types = DicUnitType::all(['code', 'name']);
        return view('jqxadmin.units', compact('unit_types'));
    }

    public function fetchUnits()
    {
        return Unit::orderBy('unit_code')->with('parent')->get();
    }

}

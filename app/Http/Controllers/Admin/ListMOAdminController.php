<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Unit;
use App\UnitList;
use App\UnitListMember;

class ListMOAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $lists = UnitList::all();
        return view('jqxadmin.unit_lists', compact('lists'));
    }

    public function fetchListMembers(int $list)
    {
        return UnitListMember::where('list_id', $list)->with('unit')->get();
    }

    public function fetchNonMembers(int $list)
    {
        $listmembers = UnitListMember::List($list)->get()->pluck('ou_id');
        return Unit::Primary()->whereNotIn('id', $listmembers)->orderBy('unit_code')->with('parent')->get();
    }
}

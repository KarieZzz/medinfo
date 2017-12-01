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

    public function store(Request $request)
    {
        $this->validate($request, [
                'name' => 'required|max:128|unique:unit_lists',
                'slug' => 'required|max:32|unique:unit_lists',
            ]
        );
        $new = UnitList::create([ 'name' => $request->name, 'slug' => $request->slug ]);
        return ['stored' => true, 'id' => $new->id];

    }

    public function update($id, Request $request)
    {
        $list = UnitList::find($id);
        if ($list->name !== $request->name) {
            $this->validate($request, [
                    'name' => 'required|max:128|unique:unit_lists',
                ]
            );
            $list->name = $request->name;
            $list->save();
        }
        if ($list->slug !== $request->slug) {
            $this->validate($request, [
                    'slug' => 'required|max:32|unique:unit_lists',
                ]
            );
            $list->slug = $request->slug;
            $list->save();
        }
        return [ 'updated' => true ];
    }

    public function destroy($list)
    {
        UnitListMember::List($list)->delete();
        $l = UnitList::destroy($list);
        return ['removed' => true, 'id' => $list, ];
    }

    public function fetchlits()
    {
        return UnitList::all();
    }

    public function fetchListMembers(int $list)
    {
        //return UnitListMember::where('list_id', $list)->with('unit')->orderBy('unit.unit_code')->get();
        //return UnitListMember::where('list_id', $list)->with(['unit' => function($query) { $query->orderBy('unit_code'); }])->get();
        $listmembers = UnitListMember::List($list)->get()->pluck('ou_id');
        return Unit::Primary()->whereIn('id', $listmembers)->orderBy('unit_code')->with('parent')->get();
    }

    public function fetchNonMembers(int $list)
    {
        $listmembers = UnitListMember::List($list)->get()->pluck('ou_id');
        return Unit::Primary()->whereNotIn('id', $listmembers)->orderBy('unit_code')->with('parent')->get();
    }

    public function addMembers(UnitList $list, Request $request)
    {
        $units = explode(",", $request->units);
        $newmembers = [];
        foreach($units as $unit) {
            $member = UnitListMember::firstOrCreate([ 'list_id' => $list->id, 'ou_id' => $unit ]);
            $newmembers[] = $member->id;
        }
        return [ 'count_of_inserted' => count($newmembers) ];
    }

    public function removeMembers($list, $members)
    {
        $removed = explode(",", $members);
        //dd($removed);
        //$removed_members = UnitListMember::List($list)->destroy($removed);
        $removed_members = UnitListMember::List($list)->whereIn('ou_id', $removed)->delete();
        return [ 'count_of_removed' => $removed_members ];
    }
}

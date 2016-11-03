<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\UnitGroup;
use App\UnitGroupMember;
use App\Document;

class UnitGroupAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $groups = UnitGroup::all();
        return view('jqxadmin.unit_groups', compact('groups'));
    }

    public function fetchGroups()
    {
       //return UnitGroup::orderBy('group_name')->get();
       return UnitGroup::all();;
    }

    public function fetchMembers(int $group)
    {
        return UnitGroupMember::where('group_id', $group)->with('unit')->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'parent_id' => 'exists:unit_groups,id',
                'group_name' => 'required|max:128|unique:unit_groups',
                'slug' => 'max:128|unique:unit_groups',
            ]
        );
        $newgroup = new UnitGroup();
        $newgroup->parent_id = empty($request->parent_id) ? null : $request->parent_id;
        $newgroup->group_name = $request->group_name;
        $newgroup->slug = empty($request->slug) ?  str_slug($newgroup->group_name) : $request->slug;
        $newgroup->save();
        try {
            $newgroup->save();
            return ['message' => 'Новая запись создана. Id:' . $newgroup->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            switch ($errorCode) {
                case 7:
                    $message = 'Запись не сохранена. Дублирующиеся значения.';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function update(UnitGroup $group, Request $request)
    {
        $this->validate($request, [
                'parent_id' => 'exists:unit_groups,id',
                'group_name' => 'required|max:128',
                'slug' => 'required|max:128|unique:unit_groups',
            ]
        );
        $group->parent_id = empty($request->parent_id) ? null : $request->parent_id;
        $group->group_name = $request->group_name;
        $group->slug = $request->slug;
        $result = [];
        try {
            $group->save();
            $result = ['message' => 'Запись id ' . $group->id . ' сохранена'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
            if($errorCode == 7){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование данных.'];
            }
        }
        return $result;
    }

    public function delete(UnitGroup $group)
    {
        $id = $group->id;
        $documents = Document::OfUnit($group->id)->delete();
        $members = UnitGroupMember::OfGroup($group->id)->delete();
        $group_deleted = $group->delete();
        if ($group_deleted) {
            $message = 'Удалена группа Id ' . $id;
        } else {
            $message = 'Ошибка удаления группы Id ' . $id;
        }
        return compact('documents', 'members', 'group_deleted', 'message');
    }

    public function addMembers(UnitGroup $group, Request $request)
    {
        $units = explode(",", $request->units);
        $newmembers = [];
        foreach($units as $unit) {
            $member = UnitGroupMember::firstOrCreate([ 'group_id' => $group->id, 'ou_id' => $unit ]);
            $newmembers[] = $member->id;
        }
        return [ 'count_of_inserted' => count($newmembers) ];
    }

    public function removeMember(UnitGroupMember $member)
    {
        $member_deleted = $member->delete();
        if ($member_deleted) {
            $message = "Медицинская организация/Территория удалена из списка";
        }
        return compact('member_deleted', 'message');
    }

}

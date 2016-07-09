<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use App\Medinfo\UnitTree;

class MOAdminController extends Controller
{
    //
    public function fetch_mo_hierarchy($parent = 0)
    {
        $mo_tree = UnitTree::getSimpleTree($parent);
        return $mo_tree;
    }

    private function get_childs($parent) {
        $lev_query = "select id, parent_id, unit_code, unit_name from mo_hierarchy where parent_id = $parent";
        $res = DB::select($lev_query);
        $units = array();
        if (count($res) > 0) {
            foreach ($res as $r) {
                $units[] = $r;
                $units = array_merge($units, $this->get_childs($r->id) );
            }
        }
        return $units;
    }


}

<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 29.06.2016
 * Time: 15:15
 */

namespace App\Medinfo;
use App\Document;
use DB;
use phpDocumentor\Reflection\Types\Self_;

class DocumentTreeByOU
{

    public static function get_units($ou)
    {
        $units[] = $ou;
        return array_merge($units, self::tree_element($ou));

    }

    private static function tree_element($parent) {

        $lev_query = "SELECT id FROM mo_hierarchy WHERE parent_id = $parent";
        $res = DB::select($lev_query);
        $units = [];
        if (count($res) > 0) {
            foreach ($res as $r) {
                $units[] = $r->id;
                $units = array_merge($units, self::tree_element($r->id));
            }
        }
        return $units;
    }

    public static function get($ou)
    {
        $units = self::get_units($ou);
        $d = Document::whereIn('ou_id', $units)->pluck('id');
        return $d;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 07.06.2016
 * Time: 18:07
 */

namespace App\Medinfo;


use App\Unit;
use App\WorkerScope;

class UnitTree
{
    public $top_level_id;
    private $tree;
    private $folder_icon = null;
    private $unit_icon = null;

    public function __construct($top_level_id = null)
    {
        if (is_null($top_level_id)) {
            throw new \Exception("Не определен Id организации/территории/группы верхнего уровня");
        }
        $this->top_level_id = $top_level_id;
    }

    public static function getSimpleTree($parent = 0, bool $get_only_legal = false)
    {
        $addwhere = '';
        if ($get_only_legal) {
            $addwhere  = ' AND node_type IN (1,2,3) ';
        }
        $mo_tree = self::getChilds($parent, $addwhere);
        $this_one = \DB::select("SELECT id, parent_id, unit_code, unit_name FROM mo_hierarchy WHERE id = $parent");
        $mo_tree = array_merge($mo_tree, $this_one);
        return $mo_tree;
    }

    public static function getChilds($parent, $addwhere = '')
    {
        $lev_query = "SELECT id, parent_id, unit_code, unit_name FROM mo_hierarchy WHERE blocked = 0 AND parent_id = $parent $addwhere ORDER BY unit_code";
        $res = \DB::select($lev_query);
        $units = array();
        if (count($res) > 0) {
            foreach ($res as $r) {
                $units[] = $r;
                $units = array_merge($units, self::getChilds($r->id, $addwhere));
            }
        }
        return $units;
    }

    public static function getMoTree($parent)
    {
        $units = array();
/*        $this_one = \DB::select("SELECT id, parent_id, unit_code, unit_name FROM mo_hierarchy WHERE id = $parent
          UNION SELECT id, NULL AS parent_id, slug AS unit_code, name AS unit_name FROM unit_lists WHERE id = $parent");*/
        $this_one = \DB::select("SELECT id, parent_id, unit_code, unit_name FROM mo_hierarchy WHERE id = $parent");
        $units = array_merge($units, $this_one);
/*        $lev_query = "SELECT id, parent_id, unit_code, unit_name FROM mo_hierarchy WHERE blocked = 0 AND parent_id = $parent
            UNION SELECT id, 0 as parent_id, slug AS unit_code, name AS unit_name FROM unit_lists 
            ORDER BY 2";*/
        $lev_query = "SELECT id, parent_id, unit_code, unit_name FROM mo_hierarchy WHERE blocked = 0 AND parent_id = $parent ORDER BY unit_code";
        $res = \DB::select($lev_query);
        if (count($res) > 0) {
            foreach ($res as $r) {
                $units[] = $r;
                $units = array_merge($units, self::getChilds($r->id));
            }
        }
        return $units;
    }

    public static function getMoTreeByWorker($worker)
    {
        $wscopes = WorkerScope::Worker($worker)->get();
        if (!$wscopes) {
            throw new \Exception('У пользователя не определен доступ к организационным единицам');
        }
        if ($wscopes->count() === 1) {
            return self::getMoTree($wscopes[0]->ou_id);
        }
        $units = [];
        //$root = Unit::Root()->get()->toArray();
        //$units = array_merge($units, $root);
        foreach ($wscopes as $wscope) {
            $this_one = Unit::where('id', $wscope->ou_id)->get()->toArray();
            $this_one[0]['parent_id'] = null;
            $units = array_merge($units, $this_one);
            $lev_query = "SELECT id, parent_id, unit_code, unit_name FROM mo_hierarchy WHERE blocked = 0 AND parent_id = {$this_one[0]['id']} ORDER BY unit_code";
            $res = \DB::select($lev_query);
            if (count($res) > 0) {
                foreach ($res as $r) {
                    $units[] = $r;
                    $units = array_merge($units, self::getChilds($r->id));
                }
            }
        }
        //dd($units);
        return $units;
    }

    public static function getParents($ou_id)
    {
        $parent_query = "select parent_id from mo_hierarchy where id = $ou_id";
        $res = \DB::selectOne($parent_query);
        $units = array();
        if ($res->parent_id !== null) {
            $units[] = $res->parent_id;
            $units = array_merge($units, self::getParents($res->parent_id));
        }
        return $units;
    }

    public function setHtmlTree()
    {
        $this->tree = '';
        $lev_1_query = "select id, unit_name as label, report from mo_hierarchy where id = '$this->top_level_id' order by unit_code";
        //var_dump($lev_1_query);
        $res = \DB::selectOne($lev_1_query);
        if ($res->report == 0) {
            $icon = $this->folder_icon;
        }
        else {
            $icon = $this->unit_icon;
        }
        $this->tree .= '<ul>';
        $this->tree .= "<li id='$res->id' item-expanded='true'>$icon<span item-title='true'>{$res->label}</span>";
        $this->tree .= $this->tree_element($this->top_level_id);
        $this->tree .= '</ul>';
    }

    private function tree_element($parent) {
        $lev_query = "select id, parent_id parent, unit_name as label, report  from mo_hierarchy where parent_id = $parent order by unit_code";
        $res = \DB::select($lev_query);
        $tree_level = '';
        if (count($res) > 0) {
            $tree_level = '<ul>';
            foreach ($res as $r) {
                $icon = $this->folder_icon;
                if ($r->report == 1) {
                    $icon = $this->unit_icon;
                }
                $tree_level .= "<li id='$r->id'>$icon<span item-title='true'>$r->label</span>";
                $tree_level .= $this->tree_element($r->id);
                $tree_level .= '</li>';
            }
            $tree_level .= '</ul>';
        }
        return $tree_level;
    }

    public function setFolderIcon($tag)
    {
        $this->folder_icon = $tag;
    }

    public function setUnitIcon($tag)
    {
        $this->unit_icon = $tag;
    }

    public function getTree()
    {
        return $this->tree;
    }
}
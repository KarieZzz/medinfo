<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 07.06.2016
 * Time: 18:07
 */

namespace App\Medinfo;


class UnitTree
{
    public $top_level_id;
    private $tree;
    private $folder_icon = null;
    private $unit_icon = null;

    public function __construct($top_level_id = null)
    {
        if (is_null($top_level_id)) {
            throw new Exception("Не определен Id организации/территории/группы верхнего уровня");
        }
        $this->top_level_id = $top_level_id;
    }

    public static function getSimpleTree($parent = 0)
    {
        if ($parent == 0) {
            $mo_tree = \DB::select("select id, parent_id, unit_code, unit_name from mo_hierarchy where blocked = 0 ORDER BY unit_code");
        } else {
            $mo_tree = self::getChilds($parent);
            $this_one = \DB::select("select id, parent_id, unit_code, unit_name from mo_hierarchy where id = $parent");
            $mo_tree = array_merge($mo_tree, $this_one);
        }
        return $mo_tree;
    }

    public static function getChilds($parent)
    {
        $lev_query = "select id, parent_id, unit_code, unit_name from mo_hierarchy where parent_id = $parent";
        $res = \DB::select($lev_query);
        $units = array();
        if (count($res) > 0) {
            foreach ($res as $r) {
                $units[] = $r;
                $units = array_merge($units, self::getChilds($r->id));
            }
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
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    //
    protected $table = 'mo_hierarchy';
    protected $fillable = [
        'parent_id', 'unit_code', 'inn', 'node_type', 'report', 'aggregate', 'unit_name', 'blocked',
    ];

    public function workerScope()
    {
        return $this->hasMany('App\WorkerScope', 'ou_id', 'id');
    }

    public static function getDescendants($parent) {
        $units[] = $parent;
        $lev_query = "select id from mo_hierarchy where parent_id = $parent";
        $res = \DB::select($lev_query);
        if (count($res) > 0) {
            foreach ($res as $r) {
                $units = array_merge($units, self::getDescendants($r->id));
            }
        }
        return $units;
    }

}

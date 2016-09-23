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

    public function parent()
    {
        return $this->belongsTo('App\Unit', 'parent_id', 'id');
    }

    // Выбор Юрлиц
    public function scopeLegal($query)
    {
        return $query->where('node_type', 3);
    }
    // Только незаблокированные единицы
    public function scopeActive($query)
    {
        return $query->where('blocked', 0);
    }
    // Единицы по которым может производится сведение данных
    public function scopeMayBeAggregate($query)
    {
        return $query->where('aggregate', 1);
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

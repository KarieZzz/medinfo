<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    //
    protected $table = 'mo_hierarchy';
    protected $fillable = [
        'parent_id', 'unit_code','territory_type' ,'inn', 'node_type', 'report', 'aggregate', 'unit_name', 'blocked', 'countryside',
    ];

    public function workerScope()
    {
        return $this->hasMany('App\WorkerScope', 'ou_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Unit', 'parent_id', 'id');
    }

    public function groups()
    {
        return $this->hasMany('App\UnitGroupMember', 'ou_id', 'id');
    }

    // Выбор Территорий
    public function scopeTerritory($query)
    {
        return $query->where('node_type', 2);
    }

    // Выбор Юрлиц
    public function scopeLegal($query)
    {
        return $query->where('node_type', 3);
    }

    // Выбор Обособленных подразделений
    public function scopeSubLegal($query)
    {
        return $query->where('node_type', 4);
    }

    // Выбор учреждений образования и социальной защиты
    public function scopeSchoolAndSocial($query)
    {
        return $query->where('node_type', 6);
    }

    // Все подразделения с первичными отчетами
    public function scopePrimary($query)
    {
        return $query->where('node_type', 3)->orWhere('node_type', 4)->orWhere('node_type', 6);
    }

    public function scopeUpperLevels($query)
    {
        return $query->where('node_type', 1)->orWhere('node_type', 2);
    }

    // Только незаблокированные единицы
    public function scopeActive($query)
    {
        return $query->where('blocked', 0);
    }
    // Единицы по которым может производится сведение данных
    public function scopeMayBeAggregate($query)
    {
        return $query
            ->where('aggregate', 1)
            ->orWhere('node_type', 1)
            ->orWhere('node_type', 2);
    }

    public function scopeOfCountry($query)
    {
        return $query
            ->where('countryside', true);
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

    public static function getPrimaryDescendants($parent) {
        $units = [];
        $lev_query = "SELECT * FROM mo_hierarchy WHERE parent_id = $parent";
        $res = \DB::select($lev_query);
        if (count($res) > 0) {
            foreach ($res as $r) {
                if ($r->node_type == 3 || $r->node_type == 4) {
                    $units[] = Unit::find($r->id);
                }
                if ($r->aggregate) {
                    $units = array_merge($units, self::getPrimaryDescendants($r->id));
                }
            }
        }
        return $units;
    }

}
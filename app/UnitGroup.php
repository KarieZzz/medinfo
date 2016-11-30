<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitGroup extends Model
{
    //
    protected $fillable = ['parent_id', 'group_code', 'group_name', 'slug'];
    // зарезервированные имена для "статических" групп
    public static $reserved_slugs = [ 'сводные', 'первичные', 'оп', 'обособподр', 'юл', 'юрлица', 'тер', 'территории'];

    public function members()
    {
        return $this->hasMany('App\UnitGroupMember', 'group_id', 'id');
    }

    public function scopeOfSlug($query, $slug)
    {
        return $query
            ->where('slug', $slug);
    }
}

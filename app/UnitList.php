<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitList extends Model
{
    //
    protected $fillable = ['name', 'slug', 'on_frontend'];
    // зарезервированные имена для "статических" групп
    public static $reserved_slugs = [
        'n/a',
        'первичные',
        'сводные',
        'оп',
        'обособподр',
        'юл',
        'юрлица',
        'тер',
        'территории',
        'п0', 'п1', 'п2', 'п3', 'п4', 'п5', 'п6', 'п7', 'п8', 'п9', 'п10', 'п11', 'п12', // 9-21 периоды месячные
        'пi', 'пii', 'пiii', 'пiv', // 22-25 периоды квартальные
        'село'
    ];
    const PRIMARY   = 1;
    const AGGREGATE = 2;
    const DETACHED  = 3;
    const LEGAL     = 5;
    const TERRITORY = 7;

    public function scopeSlug($query, $slug)
    {
        return $query
            ->where('slug', $slug);
    }

    public function scopeOnFrontend($query)
    {
        return $query
            ->where('on_frontend', 1);
    }

    public function members()
    {
        return $this->hasMany('App\UnitListMember', 'list_id', 'id');
    }
}

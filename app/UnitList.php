<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitList extends Model
{
    //
    protected $fillable = ['name', 'slug'];

    public function scopeSlug($query, $slug)
    {
        return $query
            ->where('slug', $slug);
    }

    public function members()
    {
        return $this->hasMany('App\UnitListMember', 'list_id', 'id');
    }
}

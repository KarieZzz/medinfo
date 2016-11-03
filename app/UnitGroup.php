<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitGroup extends Model
{
    //
    protected $fillable = ['parent_id', 'group_name', 'slug'];

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

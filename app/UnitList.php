<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitList extends Model
{
    //
    protected $fillable = ['name', 'slug', 'on_frontend'];

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

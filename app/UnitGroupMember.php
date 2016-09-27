<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitGroupMember extends Model
{
    //
    protected $fillable = ['group_id', 'ou_id' ];

    public function unit()
    {
        return $this->belongsTo('App\Unit', 'ou_id', 'id');
    }

    public function unitgroup()
    {
        return $this->belongsTo('App\UnitGroup' , 'group_id', 'id' );
    }

    public function scopeOfGroup($query, $group)
    {
        return $query->where('group_id', $group);
    }

}
